<?php

namespace Phpactor\WorseReflection;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\NullableType;
use Phpactor\WorseReflection\Core\Type\PrimitiveType;

class TypeUtil
{
    public static function isDefined(Type $type): bool
    {
        return !$type instanceof MissingType;
    }

    public static function short(Type $type): string
    {
        if ($type instanceof NullableType) {
            return '?' . self::short($type->type);
        }

        if ($type instanceof ClassType) {
            return $type->name()->short();
        }

        return $type->toPhpString();
    }

    public static function isPrimitive(Type $type): bool
    {
        return $type instanceof PrimitiveType;
    }

    public static function isClass(Type $type): bool
    {
        return $type instanceof ClassType;
    }

    public static function isNullable(Type $type): bool
    {
        return $type instanceof NullableType;
    }

    /**
     * @return ClassType[]
     */
    public static function unwrapClassTypes(Type $type): array
    {
        if ($type instanceof ClassType) {
            return [$type];
        }

        if ($type instanceof NullableType) {
            return self::unwrapClassTypes($type->type);
        }

        return [];
    }

    public static function unwrapNullableType(Type $type): Type
    {
        if (!$type instanceof NullableType) {
            return $type;
        }

        return $type->type;
    }
}
