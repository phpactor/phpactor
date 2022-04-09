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

    public function plus(NumericType $numericType): NumericType
    {
        return $this;
    }

    public function modulo(NumericType $numericType)
    {
        return $this;
    }

    public function divide(NumericType $numericType)
    {
        return $this;
    }

    public function multiply(NumericType $numericType)
    {
        return $this;
    }

    public function minus(NumericType $numericType)
    {
        return $this;
    }
}
