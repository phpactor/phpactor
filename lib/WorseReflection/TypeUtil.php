<?php

namespace Phpactor\WorseReflection;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
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

    public static function unwrapNullableType(?Type $type): Type
    {
        if (null === $type) {
            return new MissingType();
        }
        if (!$type instanceof NullableType) {
            return $type;
        }

        return $type->type;
    }

    public static function toLocalType(ReflectionScope $scope, Type $type): Type
    {
        if ($type instanceof NullableType) {
            return new NullableType(self::toLocalType($scope, $type->type));
        }
        if ($type instanceof GenericClassType) {
            $typeName = $scope->resolveLocalName($type->name());
            $newType = clone $type;
            $newType->name = ClassName::fromString($typeName);
            $newType->arguments = array_map(fn (Type $type) => self::toLocalType($scope, $type), $type->arguments);

            return $newType;
        }
        if ($type instanceof ClassType) {
            $typeName = $scope->resolveLocalName($type->name());
            $type = clone $type;
            $type->name = ClassName::fromString($typeName);
            return $type;
        }
        if ($type instanceof ArrayType) {
            $newType = clone $type;
            $newType->keyType = self::toLocalType($scope, $type->keyType);
            $newType->valueType = self::toLocalType($scope, $type->valueType);
            return $newType;
        }

        return $type;
    }
}
