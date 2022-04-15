<?php

namespace Phpactor\WorseReflection;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\BooleanLiteralType;
use Phpactor\WorseReflection\Core\Type\BooleanType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\Generalizable;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\IntType;
use Phpactor\WorseReflection\Core\Type\Literal;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\NullType;
use Phpactor\WorseReflection\Core\Type\NullableType;
use Phpactor\WorseReflection\Core\Type\NumericType;
use Phpactor\WorseReflection\Core\Type\PrimitiveType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Type\UnionType;

class TypeUtil
{
    public static function isDefined(?Type $type): bool
    {
        if (null === $type) {
            return false;
        }
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

    public static function toLocalType(Type $type, ?ReflectionScope $scope = null): Type
    {
        if (null === $scope) {
            if (!$type instanceof ReflectedClassType) {
                return $type;
            }
            $reflection = $type->reflectionOrNull();
            if (null === $reflection) {
                return $type;
            }
            $scope = $reflection->scope();
        }

        if ($type instanceof NullableType) {
            return new NullableType(self::toLocalType($type->type, $scope));
        }
        if ($type instanceof GenericClassType) {
            $typeName = $scope->resolveLocalName($type->name());
            $newType = clone $type;
            $newType->name = ClassName::fromString($typeName);
            $newType->arguments = array_map(fn (Type $type) => self::toLocalType($type, $scope), $type->arguments);

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
            $newType->keyType = self::toLocalType($type->keyType, $scope);
            $newType->valueType = self::toLocalType($type->valueType, $scope);
            return $newType;
        }

        return $type;
    }

    public static function combine(Type ...$types): Type
    {
        if (count($types) === 0) {
            return new MissingType();
        }
        if (count($types) === 1) {
            return $types[0];
        }

        return new UnionType(...$types);
    }

    /**
     * @return Type[]
     */
    public static function unwrapUnion(Type $type): array
    {
        if (!$type instanceof UnionType) {
            return [$type];
        }

        $types = [];
        foreach ($type->types as $type) {
            if ($type instanceof UnionType) {
                $types = array_merge(self::unwrapUnion($type));
                continue;
            }
            $types[] = $type;
        }

        return $types;
    }

    public static function firstDefined(Type ...$types): Type
    {
        if (empty($types)) {
            return TypeFactory::undefined();
        }

        foreach ($types as $type) {
            if (self::isDefined($type)) {
                return $type;
            }
        }

        return $type;
    }

    /**
     * If the given type is a literal, return the general type
     */
    public static function generalize(Type $type): Type
    {
        if ($type instanceof Generalizable) {
            return $type->generalize();
        }

        return $type;
    }

    /**
     * @return mixed
     */
    public static function valueOrNull(Type $type)
    {
        if ($type instanceof Literal) {
            return $type->value();
        }

        return null;
    }

    public static function toBool(Type $type): BooleanType
    {
        if ($type instanceof Literal) {
            return new BooleanLiteralType((bool)$type->value());
        }
        if ($type instanceof NullType) {
            return new BooleanLiteralType(false);
        }
        if ($type instanceof BooleanType) {
            return $type;
        }

        return new BooleanType();
    }

    public static function toNumber(Type $type): NumericType
    {
        if ($type instanceof Literal) {
            $value = (string)$type->value();
            return TypeFactory::fromNumericString($value);
        }
        return new IntType();
    }

    public static function trinaryToBoolean(Trinary $trinary): BooleanType
    {
        if ($trinary->isTrue()) {
            return new BooleanLiteralType(true);
        }
        if ($trinary->isFalse()) {
            return new BooleanLiteralType(false);
        }

        return new BooleanType();
    }
}
