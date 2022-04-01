<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset as CoreReflectionOffset;

final class ReflectionOffset implements CoreReflectionOffset
{
    private Frame $frame;
    
    private NodeContext $symbolContext;

    private function __construct(Frame $frame, NodeContext $symbolContext)
    {
        $this->frame = $frame;
        $this->symbolContext = $symbolContext;
    }

    public static function fromFrameAndSymbolContext($frame, $symbolContext)
    {
        return new self($frame, $symbolContext);
    }

    public function frame(): Frame
    {
        return $this->frame;
    }

    public function symbolContext(): NodeContext
    {
        return $this->symbolContext;
    }
}
