<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

class IterablePrimitiveType implements Type
{
    public function __toString(): string
    {
        return 'iterable';
    }

    public function toPhpString(): string
    {
        return 'iterable';
    }

    public function accepts(Type $type): Trinary
    {
        if ($type instanceof self) {
            return Trinary::true();
        }

        if ($type instanceof ReflectedClassType) {
            return $type->instanceOf(ClassName::fromString('Iterable'));
        }

        return Trinary::false();
    }
}
