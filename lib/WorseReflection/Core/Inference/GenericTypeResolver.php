<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Generator;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\TemplateMap;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;

class GenericTypeResolver
{
    /**
     * Resolve the templated type for a class member.
     * Allow passing of $memberType to override the type of the method
     * (for example because the type was overridden/updated in the frame)
     */
    public function resolveMemberType(Type $classType, ReflectionMember $member, ?Type $memberType = null): Type
    {
        $memberType = $memberType ?: $member->inferredType();

        if (!$classType instanceof ReflectedClassType) {
            return $memberType;
        }

        // not sure why this would happen
        if ($classType->name() != $member->class()->name()) {
            return $memberType;
        }

        $declaringClass = $this->declaringClass($member);
        $classGenericType = $this->resolveDeclaringClassGenericType(
            $member->class(),
            $declaringClass,
            $classType
        );

        if (!$classGenericType instanceof GenericClassType) {
            return $memberType;
        }

        $templateMap = $declaringClass->templateMap();

        $memberType = $this->mapTypes($memberType, $templateMap, $classGenericType);

        if ($templateMap->has($memberType->short())) {
            return $templateMap->get($memberType->short(), $classGenericType->arguments());
        }

        return $memberType;
    }

    private function resolveDeclaringClassGenericType(
        ReflectionClassLike $current,
        ReflectionClassLike $target,
        Type $type
    ): ?Type {
        if ($current->name() == $target->name()) {
            return $type;
        }
        foreach ($this->ancestors($current) as $ancestorType) {
            $reflectionClassLike = $ancestorType->reflectionOrNull();

            if (!$reflectionClassLike) {
                continue;
            }

            $ancestorType = $this->mapTypes($ancestorType, $current->templateMap(), $type);

            if (null !== $resolvedType = $this->resolveDeclaringClassGenericType(
                $reflectionClassLike,
                $target,
                $ancestorType
            )) {
                return $resolvedType;
            }
        }

        return null;
    }

    /**
     * @return Generator<GenericClassType>
     */
    private function ancestors(ReflectionClassLike $current): Generator
    {
        if ($current instanceof ReflectionInterface) {
            foreach ($current->docblock()->extends() as $extend) {
                if (!$extend instanceof GenericClassType) {
                    continue;
                }
                yield $extend;
            }
        }

        if ($current instanceof ReflectionClass) {
            foreach ($current->docblock()->extends() as $extend) {
                if (!$extend instanceof GenericClassType) {
                    continue;
                }
                yield $extend;
            }
            foreach ($current->docblock()->implements() as $extend) {
                if (!$extend instanceof GenericClassType) {
                    continue;
                }
                yield $extend;
            }
        }
    }

    private function mapTypes(
        Type $memberType,
        TemplateMap $templateMap,
        Type $genericClassType
    ): Type {
        return $memberType->map(function (Type $argument) use ($templateMap, $genericClassType) {
            if (!$argument instanceof ClassType) {
                return $argument;
            }

            if ($genericClassType instanceof GenericClassType) {
                if ($templateMap->has($argument->short())) {
                    return $templateMap->get($argument->short(), $genericClassType->arguments());
                }
            }

            return $argument;
        });
    }

    private function declaringClass(ReflectionMember $member): ReflectionClassLike
    {
        $reflectionClass = $member->declaringClass();

        if (!$reflectionClass instanceof ReflectionClass) {
            return $reflectionClass;
        }

        $interface = self::searchInterfaces($reflectionClass->interfaces(), $member->name());

        if (!$interface) {
            return $reflectionClass;
        }

        return $interface;
    }

    private static function searchInterfaces(ReflectionInterfaceCollection $collection, string $memberName): ?ReflectionInterface
    {
        foreach ($collection as $interface) {
            if ($interface->methods()->has($memberName)) {
                return $interface;
            }

            if (null !== $interface = self::searchInterfaces($interface->parents(), $memberName)) {
                return $interface;
            }
        }

        return null;
    }
}
