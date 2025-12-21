<?php

namespace Phpactor\Extension\CodeTransform\Rpc;

use Phpactor\ClassFileConverter\Domain\FilePath;
use Phpactor\ClassFileConverter\Domain\FileToClass;
use Phpactor\CodeTransform\Domain\ClassName;
use Phpactor\CodeTransform\Domain\Generators;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\Response\Input\ConfirmInput;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\Extension\Rpc\Response\Input\ChoiceInput;
use Phpactor\Extension\Rpc\Handler\AbstractHandler;
use Phpactor\Extension\Rpc\Response\ReplaceFileSourceResponse;
use RuntimeException;

abstract class AbstractClassGenerateHandler extends AbstractHandler
{
    public const PARAM_CURRENT_PATH = 'current_path';
    public const PARAM_NEW_PATH = 'new_path';
    public const PARAM_VARIANT = 'variant';
    public const PARAM_OVERWRITE_EXISTING = 'overwrite_existing';

    public function __construct(
        protected Generators $generators,
        protected FileToClass $fileToClass
    ) {
    }

    public function configure(Resolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_NEW_PATH => null,
            self::PARAM_VARIANT => null,
            self::PARAM_OVERWRITE_EXISTING => null,
        ]);
        $resolver->setRequired([
            self::PARAM_CURRENT_PATH
        ]);
    }

    public function handle(array $arguments)
    {
        if (false === $arguments[self::PARAM_OVERWRITE_EXISTING]) {
            return EchoResponse::fromMessage('Cancelled');
        }

        if (null === $arguments[self::PARAM_VARIANT]) {
            $this->requireInput(ChoiceInput::fromNameLabelChoicesAndDefault(
                self::PARAM_VARIANT,
                'Variant: ',
                (array) array_combine(
                    $this->generators->names(),
                    $this->generators->names()
                )
            ));
        }

        $this->requireInput(TextInput::fromNameLabelAndDefault(
            self::PARAM_NEW_PATH,
            $this->newMessage(),
            $arguments[self::PARAM_CURRENT_PATH],
            'file'
        ));

        if (
            $arguments[self::PARAM_NEW_PATH] &&
            null === $arguments[self::PARAM_OVERWRITE_EXISTING] &&
            file_exists($arguments[self::PARAM_NEW_PATH]) &&
            0 !== filesize($arguments[self::PARAM_NEW_PATH])
        ) {
            $this->requireInput(ConfirmInput::fromNameAndLabel(
                self::PARAM_OVERWRITE_EXISTING,
                'File exists and is not empty, overwrite?'
            ));
        }

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        $code = $this->generate($arguments);

        $this->writeFileContents($arguments, $code);

        return ReplaceFileSourceResponse::fromPathAndSource(
            ($code->uri()->scheme() === 'file' && $code->uri()->path()) ? $code->uri()->path() : $arguments[self::PARAM_NEW_PATH],
            (string) $code
        );
    }

    abstract protected function generate(array $arguments): SourceCode;

    abstract protected function newMessage(): string;

    protected function className(string $path)
    {
        $candidates = $this->fileToClass->fileToClassCandidates(FilePath::fromString($path));
        return ClassName::fromString($candidates->best()->__toString());
    }

    private function writeFileContents(array $arguments, SourceCode $code): void
    {
        $newPath = $arguments[self::PARAM_NEW_PATH];
        $dirName = dirname($newPath);

        if (!file_exists($dirName)) {
            if (!@mkdir($dirName, 0777, true)) {
                throw new RuntimeException(sprintf(
                    'Could not create directory at "%s"',
                    $dirName
                ));
            }
        }

        if (!file_put_contents($newPath, (string) $code)) {
            throw new RuntimeException(sprintf(
                'Could not save file contents to "%s"',
                $newPath
            ));
        }
    }
}
