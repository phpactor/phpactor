<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

final class IntersectionType extends Type
{
    use AggregateTypeTrait;

    public function __toString(): string
    {
        return implode('&', array_map(fn (Type $type) => $type->__toString(), $this->types));
    }

    public function toPhpString(): string
    {
        return implode('&', array_map(fn (Type $type) => $type->toPhpString(), $this->types));
    }
}
