<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Application\ClassReferences;
use Phpactor\Container\SourceCodeFilesystemExtension;
use Phpactor\Rpc\Editor\ReturnAction;
use Phpactor\Rpc\Editor\ReturnOption;
use Phpactor\Rpc\Editor\ReturnChoiceAction;
use Phpactor\Rpc\Editor\EchoAction;
use Phpactor\Rpc\Editor\FileReferencesAction;
use Phpactor\Rpc\Editor\StackAction;
use Phpactor\Application\ClassMethodReferences;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Reflection\Inference\Symbol;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Reflection\Inference\SymbolInformation;

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
    private $classMethodReferences;

    /**
     * @var Reflector
     */
    private $reflector;

    public function __construct(
        Reflector $reflector,
        ClassReferences $classReferences,
        ClassMethodReferences $classMethodReferences,
        string $defaultFilesystem = SourceCodeFilesystemExtension::FILESYSTEM_GIT
    ) {
        $this->classReferences = $classReferences;
        $this->defaultFilesystem = $defaultFilesystem;
        $this->classMethodReferences = $classMethodReferences;
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

    private function methodReferences(SymbolInformation $symbolInformation)
    {
        $classType = (string) $symbolInformation->classType();
        $references = $this->classMethodReferences->findOrReplaceReferences($this->defaultFilesystem, $classType, $symbolInformation->symbol()->name());

        return $references['references'];
    }

    private function getReferences(SymbolInformation $symbolInformation)
    {
        switch ($symbolInformation->symbol()->symbolType()) {
            case Symbol::CLASS_:
                return $this->classReferences($symbolInformation);
            case Symbol::METHOD:
                return $this->methodReferences($symbolInformation);
        }

        throw new \RuntimeException(sprintf(
            'Cannot find references for symbol type "%s"',
            $symbolInformation->symbol()->symbolType()
        ));
    }
}
