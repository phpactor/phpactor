<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\AggregateType;
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
        $originalType = AggregateType::toAggregateOrIntersection($originalType);
        return $originalType->narrowTo($type);
    }

    public static function subtract(Type $type, Type $from): Type
    {
        $from = AggregateType::toAggregateOrUnion($from);
        $type = AggregateType::toAggregateOrUnion($type);

        $f = $from->new(...array_filter($from->types, function (Type $t) use ($type) {
            foreach ($type->types as $subtract) {
                if ($t->__toString() === $subtract->__toString()) {
                    return false;
                }
            }
            return true;
        }))->reduce();
        return $f;
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

    public static function acceptedByType(Type $type, Type $acceptingType): Type
    {
        $type = UnionType::toUnion($type);
        $types = [];
        foreach ($type->filter()->types as $type) {
            if (!$acceptingType->accepts($type)->isTrue()) {
                continue;
            }
            $types[] = $type;
        }

        return (new UnionType(...$types))->reduce();
    }
}
