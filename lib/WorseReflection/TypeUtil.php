<?php

namespace Phpactor\WorseReflection;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\AggregateType;
use Phpactor\WorseReflection\Core\Type\BooleanLiteralType;
use Phpactor\WorseReflection\Core\Type\BooleanType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\IntType;
use Phpactor\WorseReflection\Core\Type\Literal;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\NullType;
use Phpactor\WorseReflection\Core\Type\NumericType;
use Phpactor\WorseReflection\Core\Type\ScalarType;

class TypeUtil
{
    public static function firstDefined(Type ...$types): Type
    {
        if (empty($types)) {
            return TypeFactory::undefined();
        }

        foreach ($types as $type) {
            if ($type->isDefined()) {
                return $type;
            }
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
        if ($type instanceof Literal && $type instanceof ScalarType) {
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

    public static function shortenClassTypes(Type $type): Type
    {
        return $type->map(function (Type $type) {
            if ($type instanceof ClassType) {
                return TypeFactory::class($type->name()->short());
            }

            return $type;
        });
    }

    /**
     * @param Type[] $types
     */
    public static function generalTypeFromTypes(array $types): Type
    {
        $valueType = null;
        foreach ($types as $type) {
            $type = $type->generalize();
            if ($valueType === null) {
                $valueType = $type;
                continue;
            }

            if ($valueType != $type) {
                return new MixedType();
            }
        }

        return $valueType ?: new MissingType();
    }

    public static function contains(string $string, Type $type): bool
    {
        if ($type instanceof $string) {
            return true;
        }
        if ($type instanceof AggregateType) {
            foreach ($type->expandTypes() as $type) {
                if ($type instanceof $string) {
                    return true;
                }
            }
        }

        return false;
    }
}
