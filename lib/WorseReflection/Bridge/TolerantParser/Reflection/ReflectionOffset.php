<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset as CoreReflectionOffset;

final class ReflectionOffset implements CoreReflectionOffset
{
    private function __construct(private Frame $frame, private NodeContext $nodeContext)
    {
    }

    public static function fromFrameAndSymbolContext($frame, $nodeContext)
    {
        return new self($frame, $nodeContext);
    }

    public function frame(): Frame
    {
        return $this->frame;
    }

    public function nodeContext(): NodeContext
    {
        return $this->nodeContext;
    }
}
