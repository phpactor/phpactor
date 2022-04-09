<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

final class IntLiteralType extends IntType implements Literal, Generalizable
{
    use LiteralTrait;
    public int $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }

    /**
     * @return int
     */
    public function value()
    {
        return $this->value;
    }

    public function generalize(): Type
    {
        return new IntType();
    }

    public function identity(): NumericType
    {
        return new self(+$this->value());
    }

    public function negative(): NumericType
    {
        return new self(-$this->value());
    }

    public function withValue($value)
    {
        $new = clone $this;
        $new->value = $value;
        return $new;
    }
}
