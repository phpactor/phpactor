<?php

namespace Phpactor\WorseReflection\Core\Inference\Context;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;

final class FunctionCallContext extends NodeContext implements CallContext
{
    public function __construct(
        Symbol $symbol,
        private ByteOffsetRange $byteOffsetRange,
        private ReflectionFunction $function,
        private FunctionArguments $arguments,
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

    public function arguments(): ?FunctionArguments
    {
        return $this->arguments;
    }

    public static function create(
        Name $name,
        ByteOffsetRange $byteOffsetRange,
        ReflectionFunction $function,
        FunctionArguments $arguments,
    ): self {
        return new self(
            Symbol::fromTypeNameAndPosition(Symbol::FUNCTION, $name, $byteOffsetRange),
            $byteOffsetRange,
            $function,
            $arguments,
        );
    }

    public function callable(): ReflectionMethod|ReflectionFunction
    {
        return $this->function;
    }
}
