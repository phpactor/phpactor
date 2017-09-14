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

        $classType = (string) $symbolInformation->type();

        $references = $this->classReferences->findReferences($this->defaultFilesystem, $classType);
        $results = $references['references'];

        if (count($results) === 0) {
            return EchoAction::fromMessage('No references found');
        }

        $count = array_reduce($results, function ($count, $result) {
            $count += count($result['references']);
            return $count;
        }, 0);

        return StackAction::fromActions([
            EchoAction::fromMessage(sprintf('Found %s literal references to class "%s" using FS "%s"', $count, $classType, $filesystem)),
            FileReferencesAction::fromArray($results)
        ]);
    }

    private function getReferences(Symbol $symbol)
    {
        switch ($symbol->symbolType()) {
            case Symbol::METHOD:
                return $this->classMethodReferences->findOrReplaceReferences(
                    $this->defaultFilesystem,
                    $symbolInformation->hasClassType() ? (string) $symbolInformation->classType() : null,
                    $symbol->name()
                );
            case Symbol::CLASS_:
                return $this->classReferences->findReferences(
                    $filesystem,
                    (string) $symbolInformation->type()
                );
        }

        throw new \InvalidArgumentException(sprintf(
            'Do not know how to handle references for symbol type "%s"',
            $symbol->symbolType()
        ));
    }
}
