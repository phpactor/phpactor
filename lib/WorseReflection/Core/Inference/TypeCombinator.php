<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\UnionType;

class TypeCombinator
{
    public static function remove(Type $from, Type $type): Type
    {
        $from = UnionType::toUnion($from);

        return $from->remove($type);
    }

    public static function add(Type $originalType, Type $type): Type
    {
        $originalType = UnionType::toUnion($originalType);
        $type = UnionType::toUnion($type);

        return $originalType->merge($type);
    }
}
