<?php

namespace Phpactor\WorseReflection\Core\Inference\Context;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;

class FunctionCallContext extends NodeContext
{
    public function __construct(
        Symbol $symbol,
        private ByteOffsetRange $byteOffsetRange,
        private ReflectionFunction $function,
    ) {
        parent::__construct(
            $symbol,
            $function->inferredType()->reduce()
        );
    }

    public function range(): ByteOffsetRange
    {
        return $this->byteOffsetRange;
    }

    public function function(): ReflectionFunction
    {
        return $this->function;
    }
}
