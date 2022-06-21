<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;

class MethodTypeResolver
{
    private ReflectionMethod $method;
    
    public function __construct(ReflectionMethod $method)
    {
        $this->method = $method;
    }

    public function resolve(ReflectionClassLike $contextClass): Type
    {
        dump($contextClass->name()->__toString());
        $resolvedType = $this->getDocblockTypesFromClassOrMethod($this->method, $contextClass);

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
        $classMethodOverride = $method->class()->docblock()->methodType($method->name());

        if (($classMethodOverride->isDefined())) {
            return $classMethodOverride;
        }

        // no static support here
        return $method->docblock()->returnType();
    }

    private function getTypesFromParentClass(ReflectionClassLike $reflectionClassLike): Type
    {
        if (false === $reflectionClassLike instanceof ReflectionClass) {
            return TypeFactory::undefined();
        }

        if (null === $reflectionClassLike->parent()) {
            return TypeFactory::undefined();
        }

        $reflectionClass = $reflectionClassLike->parent();
        if (false === $reflectionClass->methods()->has($this->method->name())) {
            return TypeFactory::undefined();
        }

        return $reflectionClass->methods()->get($this->method->name())->inferredType();
    }

    private function getTypesFromInterfaces(ReflectionClassLike $reflectionClassLike): Type
    {
        if (false === $reflectionClassLike->isClass()) {
            return TypeFactory::undefined();
        }

        /** @var ReflectionClass $reflectionClass */
        $reflectionClass = $reflectionClassLike;

        /** @var ReflectionInterface $interface */
        foreach ($reflectionClass->interfaces() as $interface) {
            if ($interface->methods()->has($this->method->name())) {
                return $interface->methods()->get($this->method->name())->inferredType();
            }
        }

        return TypeFactory::undefined();
    }
}
