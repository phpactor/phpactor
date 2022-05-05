<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\UnionType;

class TypeCombinator
{
    public static function remove(Type $from, Type $type): Type
    {
        $from = UnionType::toUnion($from);

        return $from->remove($type);
    }

    // if it's a:
    //
    //
    // ??? - class and we narrow to an unknown class or interface : add an intersection
    //   Foobar => BazInterface => Foobar&BazInterface
    //
    // - type in a union type: accept the type
    // - type in a intersection type: preserve the type
    //
    // - class and we narrow to a narrower class: adopt the narrow
    //   AbstractFoobar => Foobar => Foobar
    //
    // - type that accepts the narrow type: accept the narrow type
    //   mixed => string => string
    //
    public static function narrowTo(Type $type, Type $narrowTo): Type
    {
        $narrowTo = $narrowTo->reduce();

        $resolved = [];
        $types = UnionType::toUnion($type);
        $asIntersection = !$types->contains($narrowTo) && $narrowTo instanceof ClassType;

        foreach ($types->types as $type) {
            if ($type->accepts($narrowTo)->isTrue()) {
                $resolved[] = $narrowTo;
                continue;
            }

            if ($asIntersection) {
                $resolved[] = TypeFactory::intersection($type, $narrowTo)->clean();
            }
        }

        return TypeFactory::union(...$resolved)->reduce();
    }


    public static function subtract(Type $type, Type $from): Type
    {
        $from = TypeFactory::toAggregateOrUnion($from);
        $type = TypeFactory::toAggregateOrUnion($type);

        $f = $from->withTypes(...array_filter($from->types, function (Type $t) use ($type) {
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
    public static function intersection(Type $type1, Type $type2): Type
    {
        $type1 = UnionType::toUnion($type1);
        $type2 = UnionType::toUnion($type2);

        return TypeFactory::union(...array_filter($type2->types, function (Type $t) use ($type1) {
            foreach ($type1->types as $subtract) {
                if ($t->__toString() === $subtract->__toString()) {
                    return true;
                }
            }
            return false;
        }))->reduce();
    }

    public static function acceptedByType(Type $type, Type $acceptingType): Type
    {
        $type = UnionType::toUnion($type);
        $types = [];
        foreach ($type->clean()->types as $type) {
            if (!$acceptingType->accepts($type)->isTrue()) {
                continue;
            }
            $types[] = $type;
        }

        return (new UnionType(...$types))->reduce();
    }
}
