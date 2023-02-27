<?php

namespace Phpactor\WorseReflection\Core\Inference\Context;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Type;

class MethodCallContext extends NodeContext
{
    public function __construct(
        Symbol $symbol,
        Type $type,
        Type $containerType,
        private ByteOffsetRange $memberNameRange,
        private ReflectionMethod $method
    ) {
        parent::__construct($symbol, $type, $containerType);
    }

    public function reflectionMethod(): ReflectionMethod
    {
        return $this->method;
    }

    public function memberNameRange(): ByteOffsetRange
    {
        return $this->memberNameRange;
    }
}
