<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

class NullableType implements Type
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
}
