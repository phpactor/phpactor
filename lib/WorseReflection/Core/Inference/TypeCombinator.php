<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\NotType;
use Phpactor\WorseReflection\Core\Type\UnionType;

final class TypeCombinator
{
    public static function merge(Type $first, Type $second): Type
    {
        if (!$first instanceof UnionType) {
            $first = new UnionType($first);
        }
        if (!$second instanceof UnionType) {
            $second = new UnionType($second);
        }

        return new UnionType(...array_merge($first->types, $second->types));
    }

    public static function anihilate(Type $type): Type
    {
        if ($type instanceof UnionType) {
            return $type->anihilate();
        }

        if ($type instanceof NotType) {
            return new MissingType();
        }

        return $type;
    }
}
