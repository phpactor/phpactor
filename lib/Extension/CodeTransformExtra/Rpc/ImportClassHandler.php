<?php

namespace Phpactor\Extension\CodeTransformExtra\Rpc;

use Phpactor\CodeTransform\Domain\Refactor\ImportClass\NameImport;
use Phpactor\Extension\Rpc\Response\CollectionResponse;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Handler\AbstractHandler;
use Phpactor\CodeTransform\Domain\Refactor\ImportName;
use Phpactor\Extension\SourceCodeFilesystemExtra\SourceCodeFilestem\Application\ClassSearch;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\Response\Input\ListInput;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass\NameAlreadyUsedException;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass\NameAlreadyImportedException;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Util\WordAtOffset;

class ImportClassHandler extends AbstractHandler
{
    const PARAM_NAME = 'name';
    const PARAM_OFFSET = 'offset';
    const PARAM_SOURCE = 'source';
    const PARAM_PATH = 'path';
    const PARAM_ALIAS = 'alias';
    const PARAM_QUALIFIED_NAME = 'qualified_name';
    const NAME = 'import_class';

    public function __construct(
        private readonly ImportName $nameImport,
        private readonly ClassSearch $classSearch,
        private readonly string $filesystem
    ) {
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_QUALIFIED_NAME => null,
            self::PARAM_ALIAS => null,
        ]);
        $resolver->setRequired([
            self::PARAM_OFFSET,
            self::PARAM_SOURCE,
            self::PARAM_PATH,
        ]);
    }

    public function handle(array $arguments)
    {
        if (null === $arguments[self::PARAM_QUALIFIED_NAME]) {
            $name = (new WordAtOffset(WordAtOffset::SPLIT_QUALIFIED_PHP_NAME))($arguments[self::PARAM_SOURCE], $arguments[self::PARAM_OFFSET]);
            $suggestions = $this->suggestions($name);

            if (count($suggestions) === 0) {
                return EchoResponse::fromMessage(sprintf(
                    'No classes found with name "%s"',
                    $name
                ));
            }

            if (count($suggestions) > 1) {
                $this->requireInput(
                    ListInput::fromNameLabelChoices(
                        self::PARAM_QUALIFIED_NAME,
                        'Select class:',
                        array_combine($suggestions, $suggestions)
                    )
                );
            } else {
                $arguments[self::PARAM_QUALIFIED_NAME] = reset($suggestions);
            }
        }

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        try {
            $sourceCode = $this->nameImport->importName(
                SourceCode::fromStringAndPath(
                    $arguments[self::PARAM_SOURCE],
                    $arguments[self::PARAM_PATH]
                ),
                ByteOffset::fromInt($arguments[self::PARAM_OFFSET]),
                NameImport::forClass($arguments[self::PARAM_QUALIFIED_NAME], $arguments[self::PARAM_ALIAS])
            )->apply($arguments[self::PARAM_SOURCE]);
        } catch (NameAlreadyUsedException $e) {
            if ($e instanceof NameAlreadyImportedException && $e->existingName() === $arguments[self::PARAM_QUALIFIED_NAME]) {
                return EchoResponse::fromMessage(sprintf(
                    'Class "%s" is already imported',
                    $arguments[self::PARAM_QUALIFIED_NAME]
                ));
            }

            $arguments[self::PARAM_ALIAS] = null;
            $this->requireInput(TextInput::fromNameLabelAndDefault(
                self::PARAM_ALIAS,
                sprintf(
                    '"%s" is already used, choose an alias: ',
                    $e->name()
                ),
                $e->name()
            ));

            return $this->createInputCallback($arguments);
        } catch (TransformException $e) {
            return EchoResponse::fromMessage($e->getMessage());
        }

        return CollectionResponse::fromActions([
            UpdateFileSourceResponse::fromPathOldAndNewSource(
                $arguments[self::PARAM_PATH],
                $arguments[self::PARAM_SOURCE],
                (string) $sourceCode
            ),
            EchoResponse::fromMessage(sprintf(
                'Imported class "%s"',
                $arguments[self::PARAM_QUALIFIED_NAME]
            ))
        ]);
    }

    private function suggestions(string $name)
    {
        $suggestions = $this->classSearch->classSearch(
            $this->filesystem,
            $name
        );

        return array_map(function (array $suggestion) {
            return $suggestion['class'];
        }, $suggestions);
    }
}
