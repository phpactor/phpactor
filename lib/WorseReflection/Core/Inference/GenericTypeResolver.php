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
use RuntimeException;
use Traversable;

class GenericTypeResolver
{
    /**
     * Resolve template type for methods declaring class:
     *  
     * - Get current class
     * - Descend to find the declaring class _through generic annotations_
     *   - Start with the current class's generic arguments
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

        $arguments = $this->resolveTemplateArguments(
            $member->class(),
            $member->declaringClass(),
            $classType->arguments()
        );

        $templateMap = $member->declaringClass()->templateMap();

        if ($templateMap->has($memberType->short())) {
            return $templateMap->get($memberType->short(), $arguments);
        }

        return $memberType;
    }

    /**
     * @param Type[] $arguments
     * @return null|array<Type> $arguments
     */
    private function resolveTemplateArguments(ReflectionClassLike $current, ReflectionClassLike $target, array $arguments): ?array
    {
        if ($current->name() == $target->name()) {
            return $arguments;
        }

        foreach ($this->ancestors($current) as $ancestor) {
            if (null !== $arguments = $this->resolveTemplateArguments($current, $target, [])) {
                return $arguments;
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
