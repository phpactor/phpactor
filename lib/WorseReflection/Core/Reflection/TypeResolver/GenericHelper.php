<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;

class GenericHelper
{
    public static function resolveMethodType(ReflectionClassLike $class, ReflectionClassLike $declaringClass, Type $type): Type
    {
        if (!$type instanceof ClassType) {
            return $type;
        }

        if ($type->name()->count() !== 1) {
            return $type;
        }

        $extendsType = $class->docblock()->extends();

        if ($extendsType instanceof GenericClassType) {
            $arguments = $extendsType->arguments();
            return $declaringClass->templateMap()->get($type->__toString(), $arguments);
        }

        $implements = $class->docblock()->implements();

        foreach ($implements as $implementsType) {
            if (!$implementsType instanceof GenericClassType) {
                continue;
            }
            if ($implementsType->name()->full() === $declaringClass->name()->__toString()) {
                $arguments = $implementsType->arguments();
                return $declaringClass->templateMap()->get($type->__toString(), $arguments);
            }
        }

        return $type;
    }
}
