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
}
