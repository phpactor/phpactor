<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Application\ClassReferences;
use Phpactor\Container\SourceCodeFilesystemExtension;
use Phpactor\Rpc\Response\EchoResponse;
use Phpactor\Rpc\Response\FileReferencesResponse;
use Phpactor\Rpc\Response\CollectionResponse;
use Phpactor\Application\ClassMemberReferences;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Inference\SymbolInformation;
use Phpactor\ClassMover\Domain\Model\ClassMemberQuery;
use Phpactor\Rpc\Response\Input\ChoiceInput;
use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\Rpc\Response\Input\TextInput;

class ReferencesHandler extends AbstractHandler
{
    const NAME = 'references';

    const PARAMETER_OFFSET = 'offset';
    const PARAMETER_SOURCE = 'source';
    const PARAMETER_MODE = 'mode';
    const PARAMETER_FILESYSTEM = 'filesystem';

    const MODE_FIND = 'find';
    const MODE_REPLACE = 'replace';
    const PARAMETER_REPLACEMENT = 'replacement';

    /**
     * @var ClassReferences
     */
    private $classReferences;

    /**
     * @var string
     */
    private $defaultFilesystem;

    /**
     * @var ClassMemberReferences
     */
    private $classMemberReferences;

    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var FilesystemRegistry
     */
    private $registry;

    public function __construct(
        Reflector $reflector,
        ClassReferences $classReferences,
        ClassMemberReferences $classMemberReferences,
        FilesystemRegistry $registry,
        string $defaultFilesystem = SourceCodeFilesystemExtension::FILESYSTEM_GIT
    ) {
        $this->classReferences = $classReferences;
        $this->defaultFilesystem = $defaultFilesystem;
        $this->classMemberReferences = $classMemberReferences;
        $this->reflector = $reflector;
        $this->registry = $registry;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function defaultParameters(): array
    {
        return [
            self::PARAMETER_OFFSET => null,
            self::PARAMETER_SOURCE => null,
            self::PARAMETER_MODE => self::MODE_FIND,
            self::PARAMETER_FILESYSTEM => null,
            self::PARAMETER_REPLACEMENT => null,
        ];
    }

    public function handle(array $arguments)
    {
        $offset = $this->reflector->reflectOffset(
            SourceCode::fromString($arguments[self::PARAMETER_SOURCE]),
            Offset::fromInt($arguments[self::PARAMETER_OFFSET])
        );
        $symbolInformation = $offset->symbolInformation();

        if (null === $arguments[self::PARAMETER_FILESYSTEM]) {
            $this->requireArgument(self::PARAMETER_FILESYSTEM, ChoiceInput::fromNameLabelChoicesAndDefault(
                self::PARAMETER_FILESYSTEM,
                sprintf('%s "%s" in:', ucfirst($symbolInformation->symbol()->symbolType()), $symbolInformation->symbol()->name()),
                array_combine($this->registry->names(), $this->registry->names()),
                $this->defaultFilesystem
            ));
        }

        if ($arguments[self::PARAMETER_MODE] === self::MODE_REPLACE) {
            $this->requireArgument(self::PARAMETER_REPLACEMENT, TextInput::fromNameLabelAndDefault(
                self::PARAMETER_REPLACEMENT,
                'Replacement:',
                (string) $symbolInformation->type()->className()
            ));
        }

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        switch ($arguments[self::PARAMETER_MODE]) {
            case self::MODE_FIND:
                return $this->findOrReplaceReferences($symbolInformation, $arguments['filesystem']);
            case self::MODE_REPLACE:
                return $this->findOrReplaceReferences($symbolInformation, $arguments['filesystem'], $arguments[self::PARAMETER_REPLACEMENT]);
        }

        throw new \InvalidArgumentException(sprintf(
            'Unknown references mode "%s"',
            $arguments['mode']
        ));
    }

    private function findOrReplaceReferences(SymbolInformation $symbolInformation, string $filesystem, string $replacement = null)
    {
        $references = $this->performFindOrReplaceReferences($symbolInformation, $filesystem, $replacement);

        if (count($references) === 0) {
            return EchoResponse::fromMessage('No references found');
        }

        $count = array_reduce($references, function ($count, $result) {
            $count += count($result['references']);
            return $count;
        }, 0);

        $riskyCount = array_reduce($references, function ($count, $result) {
            if (!isset($result['risky_references'])) {
                return $count;
            }
            $count += count($result['risky_references']);
            return $count;
        }, 0);

        $risky = '';
        if ($riskyCount > 0) {
            $risky = sprintf(' (%s risky references not listed)', $riskyCount);
        }

        return CollectionResponse::fromActions([
            EchoResponse::fromMessage(sprintf(
                'Found %s literal references to %s "%s" using FS "%s"%s',
                $count,
                $symbolInformation->symbol()->symbolType(),
                $symbolInformation->symbol()->name(),
                $filesystem,
                $risky
            )),
            FileReferencesResponse::fromArray($references),
        ]);
    }

    private function classReferences(string $filesystem, SymbolInformation $symbolInformation, string $replacement = null)
    {
        $classType = (string) $symbolInformation->type();
        $references = $this->classReferences->findOrReplaceReferences($filesystem, $classType);

        return $references['references'];
    }

    private function memberReferences(string $filesystem, SymbolInformation $symbolInformation, string $memberType, string $replacement = null)
    {
        $classType = (string) $symbolInformation->containerType();

        $references = $this->classMemberReferences->findOrReplaceReferences(
            $filesystem,
            $classType,
            $symbolInformation->symbol()->name(),
            $memberType,
            $replacement
        );

        return $references['references'];
    }

    private function performFindOrReplaceReferences(SymbolInformation $symbolInformation, string $filesystem, string $replacement = null)
    {
        switch ($symbolInformation->symbol()->symbolType()) {
        case Symbol::CLASS_:
            return $this->classReferences($filesystem, $symbolInformation, $replacement);
        case Symbol::METHOD:
            return $this->memberReferences($filesystem, $symbolInformation, ClassMemberQuery::TYPE_METHOD, $replacement);
        case Symbol::PROPERTY:
            return $this->memberReferences($filesystem, $symbolInformation, ClassMemberQuery::TYPE_PROPERTY, $replacement);
        case Symbol::CONSTANT:
            return $this->memberReferences($filesystem, $symbolInformation, ClassMemberQuery::TYPE_CONSTANT, $replacement);
        }

        throw new \RuntimeException(sprintf(
            'Cannot find references for symbol type "%s"',
            $symbolInformation->symbol()->symbolType()
        ));
    }
}
