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
     * -
     *
     * For method using class template parameters:
     *
     * - Resolve template map for declaring class
     */
    public function resolveMemberType(Type $classType, ReflectionMember $member): Type
    {
        $memberType = $member->inferredType();

        if (!$classType instanceof GenericClassType) {
            return $memberType;
        }

        if ($classType->name() != $member->class()->name()) {
            throw new RuntimeException(sprintf(
                'member class-type "%s" must be same as container class type "%s"',
                $member->class()->name(),
                $classType->name()
            ));
        }

        $genericClassType = $this->resolveDeclaringClassGenericType(
            $member->class(),
            $member->declaringClass(),
            $classType
        );

        $templateMap = $member->declaringClass()->templateMap();

        if ($memberType instanceof GenericClassType) {
            $memberType = $this->mapGenericType($memberType, $templateMap, $genericClassType);
        }

        if ($templateMap->has($memberType->short())) {
            return $templateMap->get($memberType->short(), $genericClassType->arguments());
        }

        return $memberType;
    }

    /**
     * @return null|GenericClassType
     */
    private function resolveDeclaringClassGenericType(
        ReflectionClassLike $current,
        ReflectionClassLike $target,
        GenericClassType $type
    ): ?Type {
        if ($current->name() == $target->name()) {
            return $type;
        }

        foreach ($this->ancestors($current) as $ancestorType) {
            $reflectionClassLike = $ancestorType->reflectionOrNull();

            if (!$reflectionClassLike) {
                continue;
            }

            $ancestorType = $this->mapGenericType($ancestorType, $current->templateMap(), $type);

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

    private function mapGenericType(
        GenericClassType $memberType,
        TemplateMap $templateMap,
        GenericClassType $genericClassType
    ): GenericClassType {
        return $memberType->withArguments(array_map(function (Type $argument) use ($templateMap, $genericClassType) {
            if (!$argument instanceof ClassType) {
                return $argument;
            }

            if ($templateMap->has($argument->short())) {
                return $templateMap->get($argument->short(), $genericClassType->arguments());
            }
            return $argument;
        }, $memberType->arguments()));
    }
}
