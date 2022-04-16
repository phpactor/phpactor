<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\TypeUtil;

class GenericHelper
{
    public static function resolveMethodType(ReflectionClassLike $class, ReflectionClassLike $declaringClass, Type $type): Type
    {
        if (!$type instanceof ClassType) {
            return $type;
        }

        $extendsType = $class->docblock()->extends();
        $extendsType = $class->scope()->resolveFullyQualifiedName($extendsType);

        if ($extendsType instanceof GenericClassType) {
            $arguments = $extendsType->arguments();
            return self::resolveGenericType($declaringClass, $type, $arguments);
        }

        $implements = $class->docblock()->implements();

        foreach ($implements as $implementsType) {
            $implementsType = $class->scope()->resolveFullyQualifiedName($implementsType);
            if (!$implementsType instanceof GenericClassType) {
                continue;
            }

            if ($implementsType->name()->full() === $declaringClass->name()->__toString()) {
                $arguments = $implementsType->arguments();
                return self::resolveGenericType($declaringClass, $type, $arguments);
            }
        }

        return $type;
    }

    /**
     * @param Type[] $arguments
     */
    private static function resolveGenericType(ReflectionClassLike $declaringClass, Type $type, array $arguments): Type
    {
        if ($type instanceof GenericClassType) {
            return $type->setArguments($arguments);
        }

        return $declaringClass->templateMap()->get(TypeUtil::short($type), $arguments);
    }
}
