<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

class PrimitiveIterableType implements Type, IterableType
{
    public function __toString(): string
    {
        return $this->toPhpString();
    }

    public function toPhpString(): string
    {
        return 'iterable';
    }

    public function iterableValueType(): Type
    {
        return new MissingType();
    }

    public function accepts(Type $type): Trinary
    {
        return Trinary::fromBoolean($type instanceof IterableType);
    }
}
