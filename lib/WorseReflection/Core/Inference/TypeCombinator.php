<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\UnionType;
use Phpactor\WorseReflection\TypeUtil;

class TypeCombinator
{
    public static function remove(Type $from, Type $type): Type
    {
        $from = self::toUnion($from);

        return $from->remove($type);
    }

    public static function add(Type $originalType, Type $type): Type
    {
        $originalType = self::toUnion($originalType);
        $type = self::toUnion($type);

        return $originalType->merge($type);
    }

    private static function toUnion(Type $type): UnionType
    {
        if ($type instanceof UnionType) {
            return $type;
        }

        return TypeFactory::union($type);
    }
}
