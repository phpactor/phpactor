<?php

namespace Phpactor\WorseReflection\Core\Type;

final class IntLiteralType extends IntType implements Literal
{
    public int $value;

    public function __construct(int $value)
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
