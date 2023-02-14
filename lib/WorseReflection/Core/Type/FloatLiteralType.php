<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

final class FloatLiteralType extends FloatType implements Literal, Generalizable
{
    use LiteralTrait;

    public function __construct(public float $value)
    {
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }

    public function value(): float
    {
        return $this->value;
    }

    public function generalize(): Type
    {
        return new FloatType();
    }

    public function identity(): NumericType
    {
        return new self(+$this->value());
    }

    public function negative(): NumericType
    {
        return new self(-$this->value());
    }

    public function withValue(mixed $value): self
    {
        $new = clone $this;
        $new->value = $value;
        return $new;
    }

    public function accepts(Type $type): Trinary
    {
        if ($type instanceof FloatLiteralType) {
            return Trinary::fromBoolean($type->equals($this));
        }
        if ($type instanceof FloatType) {
            return Trinary::maybe();
        }

        return parent::accepts($type);
    }
}
