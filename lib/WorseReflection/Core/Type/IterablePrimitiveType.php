<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;

class IterablePrimitiveType extends Type
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
            return $type->instanceof(TypeFactory::class('Iterable'));
        }

        return Trinary::false();
    }
}
