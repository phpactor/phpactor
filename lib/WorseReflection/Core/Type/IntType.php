<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;

class IntType extends NumericType implements BitwiseOperable
{
    public function toPhpString(): string
    {
        return 'int';
    }

    public function shiftRight(Type $right): Type
    {
        if ($right instanceof IntType && $right instanceof Literal && $this instanceof Literal) {
            return $this->withValue($this->value() >> $right->value());
        }

        return new BooleanType();
    }

    public function shiftLeft(Type $right): Type
    {
        if ($right instanceof IntType && $right instanceof Literal && $this instanceof Literal) {
            return $this->withValue($this->value() << $right->value());
        }

        return new BooleanType();
    }

    public function bitwiseXor(Type $right): Type
    {
        if ($right instanceof IntType && $right instanceof Literal && $this instanceof Literal) {
            return $this->withValue($this->value() ^ $right->value());
        }

        return new BooleanType();
    }

    public function bitwiseOr(Type $right): Type
    {
        if ($right instanceof IntType && $right instanceof Literal && $this instanceof Literal) {
            return $this->withValue($this->value() | $right->value());
        }

        return new BooleanType();
    }

    public function bitwiseAnd(Type $right): Type
    {
        if ($right instanceof IntType && $right instanceof Literal && $this instanceof Literal) {
            return $this->withValue($this->value() & $right->value());
        }

        return new BooleanType();
    }

    public function bitwiseNot(): Type
    {
        return $this->withValue(~$this->value());
    }
}
