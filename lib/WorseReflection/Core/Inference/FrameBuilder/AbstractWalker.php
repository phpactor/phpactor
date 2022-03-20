<?php

namespace Phpactor\WorseReflection\Core\Inference\FrameBuilder;

use Phpactor\WorseReflection\Core\Inference\FrameWalker;
use Phpactor\WorseReflection\Core\Inference\SymbolFactory;

abstract class AbstractWalker implements FrameWalker
{
    protected function symbolFactory(): SymbolFactory
    {
        return new SymbolFactory();
    }
}
