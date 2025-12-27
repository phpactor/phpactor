<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;

class MethodTypeResolver
{
    public function __construct(private readonly ReflectionMethod $method)
    {
    }

    public function resolve(ReflectionClassLike $contextClass): Type
    {
        $resolvedType = $this->getDocblockTypesFromClassOrMethod($this->method);

        if (($resolvedType->isDefined())) {
            return $resolvedType;
        }

        $resolvedType = $this->getTypesFromParentClass($contextClass);

        if (($resolvedType->isDefined())) {
            return $resolvedType;
        }

        return $this->getTypesFromInterfaces($contextClass);
    }

    private function getDocblockTypesFromClassOrMethod(ReflectionMethod $method): Type
    {
        $classLike = $method->class();
        $classMethodOverride = $classLike->docblock()->methodType($method->name());

        if (($classMethodOverride->isDefined())) {
            return $classMethodOverride;
        }
        $returnType = $method->docblock()->returnType();
        $aliased = $classLike->docblock()->typeAliases()->forType($returnType);
        if ($aliased) {
            return $aliased;
        }

        // no static support here
        return $returnType;
    }

    private function getTypesFromParentClass(ReflectionClassLike $reflectionClassLike): Type
    {
        $methodClass = $this->method->declaringClass();

        if (!$methodClass instanceof ReflectionClass) {
            return TypeFactory::undefined();
        }

        if (null === $methodClass->parent()) {
            return TypeFactory::undefined();
        }

        $parentClass = $methodClass->parent();

        if (false === $parentClass->methods($reflectionClassLike)->has($this->method->name())) {
            return TypeFactory::undefined();
        }

        return $parentClass->methods($reflectionClassLike)->get($this->method->name())->inferredType();
    }

    private function getTypesFromInterfaces(ReflectionClassLike $reflectionClassLike): Type
    {
        if (!$reflectionClassLike instanceof ReflectionClass) {
            return TypeFactory::undefined();
        }

        foreach ($reflectionClassLike->interfaces() as $interface) {
            if ($interface->methods()->has($this->method->name())) {
                return $interface->methods()->get($this->method->name())->inferredType();
            }
        }

        return TypeFactory::undefined();
    }
}
