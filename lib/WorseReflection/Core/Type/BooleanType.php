<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

class BooleanType extends ScalarType implements HasEmptyType
{
    public function toPhpString(): string
    {
        return 'bool';
    }

    public function or(BooleanType $right): BooleanType
    {
        return new BooleanType();
    }

    public function and(BooleanType $booleanType): BooleanType
    {
        return new BooleanType();
    }

    public function negate(): BooleanType
    {
        return new BooleanType();
    }

    public function xor(BooleanType $booleanType): BooleanType
    {
        return new BooleanType();
    }

    public function toTrinary(): Trinary
    {
        return Trinary::maybe();
    }

    public function isTrue(): bool
    {
        if ($this instanceof BooleanLiteralType) {
            return $this->value() === true;
        }

        return false;
    }

    public function emptyType(): Type
    {
        return new BooleanLiteralType(false);
    }
}
