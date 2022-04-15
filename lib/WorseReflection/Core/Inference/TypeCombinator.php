<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\NotType;
use Phpactor\WorseReflection\Core\Type\UnionType;

class TypeCombinator
{
    public static function remove(Type $from, Type $type): Type
    {
        $from = UnionType::toUnion($from);

        return $from->remove($type);
    }

    public static function narrowTo(Type $originalType, Type $type): Type
    {
        $originalType = UnionType::toUnion($originalType);
        return $originalType->narrowTo($type);
    }

    public static function add(Type $originalType, Type $type): Type
    {
        $originalType = UnionType::toUnion($originalType);
        $type = UnionType::toUnion($type);

        return $originalType->merge($type);
    }

    public static function applyType(Type $originalType, Type $type): Type
    {
        if ($type instanceof NotType) {

            // double negative - this should probably be smarter
            if ($type->type instanceof NotType) {
                return self::applyType($originalType, $type->type->type);
            }

            return self::remove($originalType, $type->type);
        }

        return self::narrowTo($originalType, $type);
    }

    public static function subtract(Type $type, Type $from): Type
    {
        $type = UnionType::toUnion($type);
        $from = UnionType::toUnion($from);

        return new UnionType(...array_filter($from->types, function (Type $t) use ($type) {
            foreach ($type->types as $subtract) {
                if ($t->__toString() === $subtract->__toString()) {
                    return false;
                }
            }
            return true;
        }));
    }

    /**
     * Return only those types in type2 that are in type1
     */
    public static function intersection(Type $type1, Type $type2): UnionType
    {
        $type1 = UnionType::toUnion($type1);
        $type2 = UnionType::toUnion($type2);

        return new UnionType(...array_filter($type2->types, function (Type $t) use ($type1) {
            foreach ($type1->types as $subtract) {
                if ($t->__toString() === $subtract->__toString()) {
                    return true;
                }
            }
            return false;
        }));
    }
}
