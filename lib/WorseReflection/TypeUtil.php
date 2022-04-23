<?php

namespace Phpactor\WorseReflection;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\BooleanLiteralType;
use Phpactor\WorseReflection\Core\Type\BooleanType;
use Phpactor\WorseReflection\Core\Type\Generalizable;
use Phpactor\WorseReflection\Core\Type\IntType;
use Phpactor\WorseReflection\Core\Type\Literal;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\NullType;
use Phpactor\WorseReflection\Core\Type\NumericType;
use Phpactor\WorseReflection\Core\Type\UnionType;

class TypeUtil
{
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
            if ($type->isDefined()) {
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

    /**
     * @param Type[] $types
     */
    public static function generalTypeFromTypes(array $types): Type
    {
        $valueType = null;
        foreach ($types as $type) {
            $type = TypeUtil::generalize($type);
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
}
