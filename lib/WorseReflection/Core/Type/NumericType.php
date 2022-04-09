<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;

abstract class NumericType extends ScalarType
{
    public function identity(): NumericType
    {
        return $this;
    }

    public function negative(): NumericType
    {
        return $this;
    }

    public function plus(NumericType $right): NumericType
    {
        if ($this instanceof Literal && $right instanceof Literal) {
            return $this->withValue($this->value() + $right->value());
        }
        return $this;
    }

    public function modulo(NumericType $right): NumericType
    {
        if ($this instanceof Literal && $right instanceof Literal) {
            return $this->withValue($this->value() % $right->value());
        }
        return $this;
    }

    public function divide(NumericType $right): NumericType
    {
        if ($this instanceof Literal && $right instanceof Literal) {
            return $this->withValue($this->value() / $right->value());
        }
        return $this;
    }

    public function multiply(NumericType $right): NumericType
    {
        if ($this instanceof Literal && $right instanceof Literal) {
            return $this->withValue($this->value() * $right->value());
        }
        return $this;
    }

    public function minus(NumericType $right): NumericType
    {
        if ($this instanceof Literal && $right instanceof Literal) {
            return $this->withValue($this->value() - $right->value());
        }
        return $this;
    }

    public function exp(NumericType $right): NumericType
    {
        if ($this instanceof Literal && $right instanceof Literal) {
            return $this->withValue($this->value() ** $right->value());
        }
        return $this;
    }
}
