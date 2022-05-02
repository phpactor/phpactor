<?php

namespace Phpactor\WorseReflection\Core\Type;

use Closure;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Types;

final class UnionType extends AggregateType
{
    public static function toUnion(Type $type): AggregateType
    {
        if ($type instanceof NullableType) {
            return self::toUnion($type->type)->add(TypeFactory::null());
        }
        if ($type instanceof UnionType) {
            return $type;
        }

        return new UnionType($type);
    }

    public function withTypes(Type ...$types): AggregateType
    {
        return new self(...$types);
    }

    public function new(Type ...$types): AggregateType
    {
        return new self(...$types);
    }

    public function __toString(): string
    {
        return implode('|', array_map(fn (Type $type) => $type->__toString(), $this->types));
    }

    public function toPhpString(): string
    {
        return implode('|', array_map(fn (Type $type) => $type->toPhpString(), $this->types));
    }

    public function accepts(Type $type): Trinary
    {
        $maybe = false;
        foreach ($this->types as $uType) {
            if ($uType->accepts($type)->isTrue()) {
                return Trinary::true();
            }
            if ($uType->accepts($type)->isMaybe()) {
                $maybe = true;
            }
        }

        if ($maybe) {
            return Trinary::maybe();
        }

        return Trinary::false();
    }
}
