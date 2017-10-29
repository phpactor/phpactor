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

class ReferencesHandler extends AbstractHandler
{
    const NAME = 'references';

    const PARAMETER_OFFSET = 'offset';
    const PARAMETER_SOURCE = 'source';
    const PARAMETER_MODE = 'mode';
    const PARAMETER_FILESYSTEM = 'filesystem';

    const MODE_FIND = 'find';
    const MODE_REPLACE = 'replace';

    /**
     * @var ClassReferences
     */
    private $classReferences;

    /**
     * @var string
     */
    private $defaultFilesystem;

    /**
     * @var ClassMethodReferences
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

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        switch ($arguments['mode']) {
            case self::MODE_FIND:
                return $this->findReferences($symbolInformation, $arguments['filesystem']);
        }

        throw new \InvalidArgumentException(sprintf(
            'Unknown references mode "%s"',
            $arguments['mode']
        ));
    }

    private function findReferences(SymbolInformation $symbolInformation, string $filesystem)
    {
        $references = $this->getReferences($symbolInformation, $filesystem);

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

    private function classReferences(string $filesystem, SymbolInformation $symbolInformation)
    {
        $classType = (string) $symbolInformation->type();
        $references = $this->classReferences->findReferences($filesystem, $classType);

        return $references['references'];
    }

    private function memberReferences(string $filesystem, SymbolInformation $symbolInformation, string $memberType)
    {
        $classType = (string) $symbolInformation->containerType();

        $references = $this->classMemberReferences->findOrReplaceReferences(
            $filesystem,
            $classType,
            $symbolInformation->symbol()->name(),
            $memberType
        );

        return $references['references'];
    }

    private function getReferences(SymbolInformation $symbolInformation, string $filesystem)
    {
        switch ($symbolInformation->symbol()->symbolType()) {
        case Symbol::CLASS_:
            return $this->classReferences($filesystem, $symbolInformation);
        case Symbol::METHOD:
            return $this->memberReferences($filesystem, $symbolInformation, ClassMemberQuery::TYPE_METHOD);
        case Symbol::PROPERTY:
            return $this->memberReferences($filesystem, $symbolInformation, ClassMemberQuery::TYPE_PROPERTY);
        case Symbol::CONSTANT:
            return $this->memberReferences($filesystem, $symbolInformation, ClassMemberQuery::TYPE_CONSTANT);
        }

        throw new \RuntimeException(sprintf(
            'Cannot find references for symbol type "%s"',
            $symbolInformation->symbol()->symbolType()
        ));
    }
}
