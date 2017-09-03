<?php

namespace Phpactor\Application;

use Phpactor\Application\Helper\FilesystemHelper;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Reflection\Inference\SymbolInformation;
use Phpactor\WorseReflection\Core\Reflection\Inference\Symbol;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\OffsetAction\ActionRegistry;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Offset;

class OffsetAction
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var FilesystemHelper
     */
    private $filesystemHelper;

    /**
     * @var ActionRegistry
     */
    private $actionRegistry;

    public function __construct(Reflector $reflector, ActionRegistry $actionRegistry)
    {
        $this->reflector = $reflector;
        $this->filesystemHelper = new FilesystemHelper();
        $this->actionRegistry = $actionRegistry;
    }

    public function choicesFromOffset(string $source, int $offset)
    {
        $reflectionOffset = $this->offsetFromSource($source, $offset);

        $symbolType = $reflectionOffset->symbolInformation()->symbol()->symbolType();

        return $this->actionRegistry->actionNames($symbolType);
    }

    public function performAction(string $source, int $offset, string $action)
    {
        $reflectionOffset = $this->offsetFromSource($source, $offset);
        $symbolInformation = $reflectionOffset->symbolInformation();

        $action = $this->actionRegistry->action(
            $symbolInformation->symbol()->symbolType(),
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

