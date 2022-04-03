<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\ExcludeType;
use Phpactor\WorseReflection\Core\Type\UnionType;

final class TypeCombinator
{
    public static function merge(Type $first, Type $second): Type
    {
        return new UnionType($first, $second);
    }

    public static function anihilate(Type $type): Type
    {
        if ($type instanceof UnionType) {
            return new UnionType(...array_map(
                fn(Type $type) => self::anihilate($type),
                $type->unique()->types
            ));
        }

        if ($type instanceof ExcludeType) {
            $subject = $type->type;
            if (!$subject instanceof UnionType) {
                $subject = new UnionType($subject);
            }
            return $subject->exclude($type->exclude);
        }

        return $type;
    }
}
