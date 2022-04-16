<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\TemplateMap;
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
        $templateMap = $declaringClass->templateMap();

        if ($extendsType instanceof GenericClassType) {
            $arguments = $extendsType->arguments();
            return self::resolveGenericType($class->scope(), $templateMap, $type, $arguments);
        }

        $implements = $class->docblock()->implements();

        foreach ($implements as $implementsType) {
            $implementsType = $class->scope()->resolveFullyQualifiedName($implementsType);
            if (!$implementsType instanceof GenericClassType) {
                continue;
            }

            if ($implementsType->name()->full() === $declaringClass->name()->__toString()) {
                $arguments = $implementsType->arguments();

                return self::resolveGenericType($class->scope(), $templateMap, $type, $arguments);
            }
        }

        return $type;
    }

    /**
     * @param Type[] $arguments
     */
    private static function resolveGenericType(ReflectionScope $scope, TemplateMap $templateMap, Type $type, array $arguments): Type
    {
        if (!$type instanceof GenericClassType) {
            return $templateMap->get(TypeUtil::short($type), $arguments);
        }

        // replace any unresolved template parameters with any
        // type constraint defined by the parameter declaration
        // (e.g. @template T of Foo)
        foreach ($arguments as &$argument) {
            if ($templateMap->has(TypeUtil::short($argument))) {
                $argument = $scope->resolveFullyQualifiedName($templateMap->get(TypeUtil::short($argument)));
            }
        }

        return $type->setArguments($arguments);
    }
}
