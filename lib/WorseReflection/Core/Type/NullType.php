<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

final class NullType extends PrimitiveType
{
    public function __toString(): string
    {
        return 'null';
    }

    public function toPhpString(): string
    {
        return 'null';
    }

    public function accepts(Type $type): Trinary
    {
        return Trinary::fromBoolean($type instanceof MixedType || $type instanceof NullType);
    }

    public function isNull(): bool
    {
        return true;
    }
}
