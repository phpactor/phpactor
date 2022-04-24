<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;

class NullableType extends Type
{
    public Type $type;

    public function __construct(Type $type)
    {
        $this->type = $type;
    }

    public function __toString(): string
    {
        return '?' . $this->type->__toString();
    }

    public function toPhpString(): string
    {
        return '?' . $this->type->toPhpString();
    }

    public function accepts(Type $type): Trinary
    {
        if ($type instanceof NullableType) {
            return Trinary::true();
        }

        return $this->type->accepts($type);
    }

    public function toTypes(): Types
    {
        return new Types([new NullType(), $this->type]);
    }

    public function isNull(): bool
    {
        return true;
    }

    public function isNullable(): bool
    {
        return true;
    }

    public function stripNullable(): Type
    {
        return $this->type;
    }
}
