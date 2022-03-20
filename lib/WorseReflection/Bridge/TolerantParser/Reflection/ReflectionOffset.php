<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Inference\SymbolContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset as CoreReflectionOffset;

final class ReflectionOffset implements CoreReflectionOffset
{
    private Frame $frame;
    
    private SymbolContext $symbolContext;

    private function __construct(Frame $frame, SymbolContext $symbolContext)
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

    public function symbolContext(): SymbolContext
    {
        return $this->symbolContext;
    }
}
