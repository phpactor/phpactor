<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;

final class IntersectionType extends AggregateType
{
    public function __toString(): string
    {
        return implode('&', array_map(fn (Type $type) => $type->__toString(), $this->types));
    }
    public static function toIntersection(Type $type): AggregateType
    {
        if ($type instanceof NullableType) {
            return self::toIntersection($type->type)->add(TypeFactory::null());
        }
        if ($type instanceof IntersectionType) {
            return $type;
        }

        return new IntersectionType($type);
    }

    public static function fromTypes(Type ...$types): Type
    {
        if (count($types) === 0) {
            return new MissingType();
        }
        if (count($types) === 1) {
            return $types[0];
        }

        return new IntersectionType(...$types);
    }

    public function short(): string
    {
        return implode('&', array_map(fn (Type $t) => $t->short(), $this->types));
    }

    public function withTypes(Type ...$types): AggregateType
    {
        return new self(...$types);
    }

    public function toPhpString(): string
    {
        return implode('&', array_map(fn (Type $type) => $type->toPhpString(), $this->types));
    }

    public function accepts(Type $type): Trinary
    {
        if (!$type instanceof ClassType && !$type instanceof IntersectionType) {
            return Trinary::false();
        }

        if ($type->equals($this)) {
            return Trinary::true();
        }

        if ($type instanceof ReflectedClassType) {
            foreach ($this->types as $type) {
                if ($type->instanceof($type)->isFalse()) {
                    return Trinary::false();
                }
            }
            return Trinary::true();
        }

        return Trinary::false();
    }
}
