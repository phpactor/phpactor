<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Psr\Log\LoggerInterface;

class MethodTypeResolver
{
    private ReflectionMethod $method;
    
    private LoggerInterface $logger;

    public function __construct(ReflectionMethod $method, LoggerInterface $logger)
    {
        $this->method = $method;
        $this->logger = $logger;
    }

    public function resolve(): Types
    {
        $resolvedTypes = $this->getDocblockTypesFromClassOrMethod($this->method);

        if ($resolvedTypes->count()) {
            return $resolvedTypes;
        }

        $resolvedTypes = $this->getTypesFromParentClass($this->method->class());

        if ($resolvedTypes->count()) {
            return $resolvedTypes;
        }

        return $this->getTypesFromInterfaces($this->method->class());
    }

    private function getDocblockTypesFromClassOrMethod(ReflectionMethod $method): Types
    {
        $classMethodOverrides = $method->class()->docblock()->methodTypes($method->name());

        if (Types::empty() != $classMethodOverrides) {
            return $this->resolveTypes(iterator_to_array($classMethodOverrides));
        }

        return $this->resolveTypes(iterator_to_array($method->docblock()->returnTypes()));
    }

    private function resolveTypes(array $types): Types
    {
        return Types::fromTypes(array_map(function (Type $type) {
            return $this->method->scope()->resolveFullyQualifiedName($type, $this->method->class());
        }, $types));
    }

    private function getTypesFromParentClass(ReflectionClassLike $reflectionClassLike): Types
    {
        if (false === $reflectionClassLike instanceof ReflectionClass) {
            return Types::empty();
        }

        if (null === $reflectionClassLike->parent()) {
            return Types::empty();
        }

        /** @var ReflectionClass $reflectioClass */
        $reflectionClass = $reflectionClassLike->parent();
        if (false === $reflectionClass->methods()->has($this->method->name())) {
            return Types::empty();
        }

        return $reflectionClass->methods()->get($this->method->name())->inferredTypes();
    }

    private function getTypesFromInterfaces(ReflectionClassLike $reflectionClassLike): Types
    {
        if (false === $reflectionClassLike->isClass()) {
            return Types::empty();
        }

        /** @var ReflectionClass $reflectionClass */
        $reflectionClass = $reflectionClassLike;

        /** @var ReflectionInterface $interface */
        foreach ($reflectionClass->interfaces() as $interface) {
            if ($interface->methods()->has($this->method->name())) {
                return $interface->methods()->get($this->method->name())->inferredTypes();
            }
        }

        return Types::empty();
    }
}
