<?php

namespace Phpactor\WorseReflection\Core\Inference\Context;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;

class ClassLikeContext extends NodeContext
{
    public function __construct(
        Symbol $symbol,
        private readonly ByteOffsetRange $byteOffsetRange,
        private readonly ReflectionClassLike $class
    ) {
        parent::__construct($symbol, $class->type());
    }

    public function range(): ByteOffsetRange
    {
        return $this->byteOffsetRange;
    }

    public function classLike(): ReflectionClassLike
    {
        return $this->class;
    }
}
