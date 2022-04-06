<?php

namespace Phpactor\WorseReflection\Core\Type;

final class FloatLiteralType extends FloatType implements Literal
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
}
