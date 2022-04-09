<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

class IntType extends NumericType implements BitwiseOperable
{
    public function toPhpString(): string
    {
        return 'int';
    }

    public function shiftRight(Type $right): Type
    {
    }

    public function shiftLeft(Type $right): Type
    {
    }

    public function bitwiseXor(Type $right): Type
    {
    }

    public function bitwiseOr(Type $right): Type
    {
    }

    public function bitwiseAnd(Type $right): Type
    {
    }
}
