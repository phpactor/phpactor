<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ClassNamedType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Traversable;

class GenericTypeResolver
{
    /**
     * Resolve template type for methods declaring class:
     *  
     * - Get current class
     * - Descend to find the declaring class
     * - ... passing through arguments as defined by @extends and @implements
     *
     * For method using class template parameters:
     *
     * - Resolve template map for declaring class
     */
    public function resolveMemberType(Type $subType, ReflectionMember $member): Type
    {
        $type = $member->inferredType();

        if (!$subType instanceof GenericClassType) {
            return $type;
        }

        $declaringClass = $this->resolveDeclaringClass($member->class(), $member->declaringClass());

        return $type;
    }

    private function resolveDeclaringClass(ReflectionClassLike $current, ReflectionClassLike $target): ?ReflectionClassLike
    {
        if ($current->name() == $target->name()) {
            return $target;
        }

        foreach ($this->ancestors($current) as $ancestor) {
            if (null !== $this->resolveDeclaringClass($current, $target)) {
                return $target;
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
