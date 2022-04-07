<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

final class FloatLiteralType extends FloatType implements Literal, Generalizable
{
    public float $value;

    public function __construct(float $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }

    public function value()
    {
        return $this->value;
    }

    public function generalize(): Type
    {
        return new FloatType();
    }
}
