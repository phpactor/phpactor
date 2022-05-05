<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

class IntLiteralType extends IntType implements Literal, Generalizable
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
        $new->value = (int)$value;
        return $new;
    }

    public function accepts(Type $type): Trinary
    {
        if ($type instanceof IntLiteralType) {
            return Trinary::fromBoolean($type->equals($this));
        }
        if ($type instanceof IntType) {
            return Trinary::maybe();
        }

        return parent::accepts($type);
    }
}
