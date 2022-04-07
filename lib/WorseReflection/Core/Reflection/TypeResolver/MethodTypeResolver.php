<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\TypeUtil;

class MethodTypeResolver
{
    private ReflectionMethod $method;
    
    public function __construct(ReflectionMethod $method)
    {
        $this->method = $method;
    }

    public function resolve(): Type
    {
        $resolvedType = $this->getDocblockTypesFromClassOrMethod($this->method);

        if (TypeUtil::isDefined($resolvedType)) {
            return $resolvedType;
        }

        $resolvedType = $this->getTypesFromParentClass($this->method->class());

        if (TypeUtil::isDefined($resolvedType)) {
            return $resolvedType;
        }

        return $this->getTypesFromInterfaces($this->method->class());
    }

    private function getDocblockTypesFromClassOrMethod(ReflectionMethod $method): Type
    {
        $classMethodOverride = $method->class()->docblock()->methodType($method->name());

        if (TypeUtil::isDefined($classMethodOverride)) {
            return $this->resolveType($classMethodOverride);
        }

        return $this->resolveType($method->docblock()->returnType());
    }

    private function resolveType(Type $type): Type
    {
        $type = $this->method->scope()->resolveFullyQualifiedName($type, $this->method->class());
        if (false === TypeUtil::isDefined($type)) {
            return $type;
        }

        return GenericHelper::resolveMethodType(
            $this->method->class(),
            $this->method->declaringClass(),
            $type
        );
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
                $type = GenericHelper::resolveMethodType(
                    $this->method->class(),
                    $interface,
                    $interface->methods()->get($this->method->name())->inferredType(),
                );
                return $type;
            }
        }

        return TypeFactory::undefined();
    }
}
