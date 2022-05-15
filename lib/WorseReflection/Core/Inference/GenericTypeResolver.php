<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ClassNamedType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use RuntimeException;
use Traversable;

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
            $type = $memberType->withArguments(array_map(function (Type $argument) use ($templateMap, $genericClassType) {
                if (!$argument instanceof ClassType) {
                    return $argument;
                }
                if ($templateMap->has($argument->short())) {
                    return $templateMap->get($argument->short(), $genericClassType->arguments());
                }
                return $argument;
            }, $memberType->arguments()));

            return $type;
        }

        if ($templateMap->has($memberType->short())) {
            return $templateMap->get($memberType->short(), $genericClassType->arguments());
        }

        return $memberType;
    }

    /**
     * @return null|GenericClassType
     */
    private function resolveDeclaringClassGenericType(ReflectionClassLike $current, ReflectionClassLike $target, GenericClassType $type): ?Type
    {
        if ($current->name() == $target->name()) {
            return $type;
        }

        foreach ($this->ancestors($current) as $ancestor) {
            if (null !== $type = $this->resolveDeclaringClassGenericType($ancestor, $target, $type)) {
                return $type;
            }
        }

        return null;
    }

    /**
     * @return Iterable<ReflectionInterface|ReflectionClass>
     */
    private function ancestors(ReflectionClassLike $current)
    {
        if ($current instanceof ReflectionInterface) {
            return $current->parents();
        }

        if ($current instanceof ReflectionClass) {
            $ancestors = [];
            $parent = $current->parent();
            if ($parent) {
                $ancestors[] = $parent;
            }
            foreach ($current->interfaces() as $interface) {
                $ancestors[] = $interface;
            }
            return $ancestors;
        }

        return [];
    }
}
