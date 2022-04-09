<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

final class BooleanLiteralType extends BooleanType implements Literal, Generalizable
{
    use LiteralTrait;
    private bool $value;

    public function __construct(bool $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value ? 'true' : 'false';
    }

    public function value()
    {
        return $this->value;
    }

    public function generalize(): Type
    {
        return new BooleanType();
    }

    public function or(BooleanType $right): BooleanType
    {
        if ($right instanceof BooleanLiteralType) {
            return new self($this->value || $right->value);
        }

        return new BooleanType();
    }

    public function and(BooleanType $right): BooleanType
    {
        if ($right instanceof BooleanLiteralType) {
            return new self($this->value && $right->value);
        }

        return new BooleanType();
    }

    public function negate(): BooleanType
    {
        return new self(!$this->value);
    }

    public function xor(BooleanType $booleanType): BooleanType
    {
        if ($booleanType instanceof BooleanLiteralType) {
            return new self($this->value() xor $booleanType->value());
        }

        return new BooleanType();
    }
}
