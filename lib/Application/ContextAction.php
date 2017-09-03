<?php

namespace Phpactor\Application;

use Phpactor\Application\Helper\FilesystemHelper;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Reflection\Inference\SymbolInformation;
use Phpactor\WorseReflection\Core\Reflection\Inference\Symbol;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;

class ContextAction
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var FilesystemHelper
     */
    private $filesystemHelper;

    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
        $this->filesystemHelper = new FilesystemHelper();
        $this->registerActions();
    }

    public function choicesFromOffset(string $source, int $offset)
    {
        $reflectionOffset = $this->offsetFromSource($source, $offset);

        if ($reflectionOffset->symbolInformation()->none()) {
            return [];
        }

        switch ($reflectionOffset->symbolInformation()->symbol()->symbolType()) {
            case Symbol::METHOD:
                return $this->methodChoices;
            case Symbol::CLASS_:
                return $this->classChoices;
        }

        return [];
    }

    public function performAction(string $source, int $offset, string $action)
    {
        $reflectionOffset = $this->offsetFromSource($source, $offset);

        if ($reflectionOffset->symbolInformation()->none()) {
            return [];
        }

        if (false === $this->actionRegistry->has(
            $reflectionOffset->symbol()->type(),
            $action
        )) {
            return [];
        }

        $action = $this->actionRegistry->get(
            $reflectionOffset->symbol()->type(),
            $action
        );

        $result = $action->perform($symbolInformation);

        return [
            'action' => $result->action(),
            'arguments' => $result->arguments(),
        ];
    }

    private function offsetFromSource(string $source, int $offset): ReflectionOffset
    {
        $source = $this->filesystemHelper->contentsFromFileOrStdin($source);

        return $this->reflector->reflectOffset(
            SourceCode::fromString($source),
            Offset::fromint($offset)
        );
    }
}
