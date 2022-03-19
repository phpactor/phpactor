<?php

namespace Phpactor\WorseReflection;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\PrimitiveType;

class TypeUtil
{
    public static function isDefined(Type $type): bool
    {
        return !$type instanceof MissingType;
    }

    public static function short(Type $type): string
    {
        if ($type instanceof ClassType) {
            return $type->name()->short();
        }

        return $type->__toString();
    }

    public static function isPrimitive(Type $type): bool
    {
        return $type instanceof PrimitiveType;
    }
}
