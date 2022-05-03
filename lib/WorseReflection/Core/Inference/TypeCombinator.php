<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\AggregateType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\IntersectionType;
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
    // - union then remove any types the narrow type does not accept
    //
    // - class and we narrow to an unknown class or interface : add an intersection
    //   Foobar => BazInterface => Foobar&BazInterface
    //
    // - class and we narrow to a narrower class: adopt the narrow
    //   AbstractFoobar => Foobar => Foobar
    //
    // - type that accepts the narrow type: accept the narrow type
    //   mixed => string => string
    //
    public static function narrowTo(Type $type, Type $narrowTo): Type
    {
        $narrowTo = UnionType::toUnion($narrowTo);
        if (empty($narrowTo->types)) {
            return $type;
        }
        $type = UnionType::toUnion($type);


        // filter any types not accepted by the narrow
        // $types = array_filter(
        //     $type->types,
        //     fn (Type $type) => !$narrowTo->accepts($type)->isFalse(),
        // );
        $types = $type->types;

        $resolved = [];
        // narrow the remaining ones
        foreach ($types as $type) {
            foreach ($narrowTo->types as $narrowType) {

                if ($narrowType instanceof ClassType) {

                    if ($narrowType->isInterface()->isMaybeOrTrue() || $narrowType->isUnknown()->isTrue()) {
                        $resolved[] = TypeFactory::intersection($type, $narrowType)->filter();
                        continue;
                    }
                }

                if ($type->accepts($narrowType)->isTrue()) {
                    $resolved[] = $narrowType;
                    continue;
                }
            }
        }


        return TypeFactory::union(...$resolved)->reduce();
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
