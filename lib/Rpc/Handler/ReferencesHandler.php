<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Application\ClassReferences;
use Phpactor\Container\SourceCodeFilesystemExtension;
use Phpactor\Rpc\Editor\EchoAction;
use Phpactor\Rpc\Editor\FileReferencesAction;
use Phpactor\Rpc\Editor\StackAction;
use Phpactor\Application\ClassMemberReferences;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Reflection\Inference\Symbol;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Reflection\Inference\SymbolInformation;
use Phpactor\ClassMover\Domain\Model\ClassMemberQuery;

class ReferencesHandler implements Handler
{
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

    public function __construct(
        Reflector $reflector,
        ClassReferences $classReferences,
        ClassMemberReferences $classMemberReferences,
        string $defaultFilesystem = SourceCodeFilesystemExtension::FILESYSTEM_GIT
    ) {
        $this->classReferences = $classReferences;
        $this->defaultFilesystem = $defaultFilesystem;
        $this->classMemberReferences = $classMemberReferences;
        $this->reflector = $reflector;
    }

    public function name(): string
    {
        return 'references';
    }

    public function defaultParameters(): array
    {
        return [
            'offset' => null,
            'source' => null,
        ];
    }

    public function handle(array $arguments)
    {
        $filesystem = $this->defaultFilesystem;
        $offset = $this->reflector->reflectOffset(SourceCode::fromString($arguments['source']), Offset::fromInt($arguments['offset']));
        $symbolInformation = $offset->symbolInformation();

        $references = $this->getReferences($symbolInformation);

        if (count($references) === 0) {
            return EchoAction::fromMessage('No references found');
        }

        $count = array_reduce($references, function ($count, $result) {
            $count += count($result['references']);
            return $count;
        }, 0);

        return StackAction::fromActions([
            EchoAction::fromMessage(sprintf(
                'Found %s literal references to %s "%s" using FS "%s"',
                $count,
                $symbolInformation->symbol()->symbolType(),
                $symbolInformation->symbol()->name(),
                $filesystem
            )),
            FileReferencesAction::fromArray($references)
        ]);
    }

    private function classReferences(SymbolInformation $symbolInformation)
    {
        $classType = (string) $symbolInformation->type();

        $references = $this->classReferences->findReferences($this->defaultFilesystem, $classType);
        return $references['references'];
    }

    private function memberReferences(SymbolInformation $symbolInformation, string $memberType)
    {
        $classType = (string) $symbolInformation->classType();
        $references = $this->classMemberReferences->findOrReplaceReferences($this->defaultFilesystem, $classType, $symbolInformation->symbol()->name(), $memberType);

        return $references['references'];
    }

    private function getReferences(SymbolInformation $symbolInformation)
    {
        switch ($symbolInformation->symbol()->symbolType()) {
            case Symbol::CLASS_:
                return $this->classReferences($symbolInformation);
            case Symbol::METHOD:
                return $this->memberReferences($symbolInformation, ClassMemberQuery::TYPE_METHOD);
            case Symbol::PROPERTY:
                return $this->memberReferences($symbolInformation, ClassMemberQuery::TYPE_PROPERTY);
            case Symbol::CONSTANT:
                return $this->memberReferences($symbolInformation, ClassMemberQuery::TYPE_CONSTANT);
        }

        throw new \RuntimeException(sprintf(
            'Cannot find references for symbol type "%s"',
            $symbolInformation->symbol()->symbolType()
        ));
    }
}
