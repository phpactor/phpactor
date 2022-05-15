<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Generator;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\TemplateMap;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use RuntimeException;

class GenericTypeResolver
{
    /**
     * Resolve template type for methods declaring class:
     *
     * - Get current class
     * - Descend to find the declaring class _through generic annotations_ an
     *   - Start with the current class's generic arguments
     *   - return the generic type of the declaring class with resolved arguments
     *
     * If return type is generic
     *
     * - iterate over the parameters and replace them with mapped template's arguments
     *
     * If class extends a generic type
     *
     * - Get current class
     * - Descend to implementing class
     *
     * For method using class template parameters:
     *
     * - Resolve template map for declaring class
     */
    public function resolveMemberType(Type $classType, ReflectionMember $member): Type
    {
        $memberType = $member->inferredType();

        if (!$classType instanceof ReflectedClassType) {
            return $memberType;
        }

        // not sure why this would happen
        if ($classType->name() != $member->class()->name()) {
            return $memberType;
        }

        $genericClassType = $this->resolveDeclaringClassGenericType(
            $member->class(),
            $member->declaringClass(),
            $classType
        );

        if (null === $genericClassType) {
            return $memberType;
        }

        $templateMap = $member->declaringClass()->templateMap();

        $memberType = $this->mapTypes($memberType, $templateMap, $genericClassType);

        if ($templateMap->has($memberType->short())) {
            return $templateMap->get($memberType->short(), $genericClassType->arguments());
        }

        return $memberType;
    }

    /**
     * @return null|ReflectedClassType
     */
    private function resolveDeclaringClassGenericType(
        ReflectionClassLike $current,
        ReflectionClassLike $target,
        ReflectedClassType $type
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

            if (null !== $type = $this->resolveDeclaringClassGenericType($reflectionClassLike, $target, $ancestorType)) {
                return $ancestorType;
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
        ReflectedClassType $genericClassType
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
}
