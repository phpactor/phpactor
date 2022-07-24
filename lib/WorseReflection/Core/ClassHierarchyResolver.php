<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;

final class ClassHierarchyResolver
{
    /**
     * @return ReflectionClassLike[]
     */
    public function resolve(ReflectionClassLike $classLike, array $resolved = []): array
    {
        return array_reverse($this->doResolve($classLike, $resolved));
    }

    /**
     * @return ReflectionClassLike[]
     */
    public function doResolve(ReflectionClassLike $classLike, array $resolved = []): array
    {
        if (isset($resolved[$classLike->name()->__toString()])) {
            return $resolved;
        }
        $resolved[$classLike->name()->__toString()] = $classLike;

        if ($classLike instanceof ReflectionClass) {
            return $this->resolveReflectionClass($classLike, $resolved);
        }

        if ($classLike instanceof ReflectionInterface) {
            return $this->resolveReflectionInterface($classLike, $resolved);
        }

        if ($classLike instanceof ReflectionTrait) {
            return $this->resolveReflectionTrait($classLike, $resolved);
        }

        return $resolved;
    }

    /**
     * @return ReflectionClassLike[]
     */
    private function resolveReflectionInterface(ReflectionInterface $classLike, array $resolved): array
    {
        foreach ($classLike->parents() as $interface) {
            $resolved = $this->doResolve($interface, $resolved);
        }
        return $resolved;
    }

    /**
     * @return ReflectionClassLike[]
     */
    private function resolveReflectionClass(ReflectionClass $classLike, array $resolved): array
    {
        $parent = $parent = $classLike->parent();
        if ($parent) {
            $resolved = $this->doResolve($parent, $resolved);
        }

        foreach ($classLike->interfaces() as $interface) {
            $resolved = $this->doResolve($interface, $resolved);
        }

        foreach ($classLike->traits() as $interface) {
            $resolved = $this->doResolve($interface, $resolved);
        }

        return $resolved;
    }

    /**
     * @return ReflectionClassLike[]
     */
    private function resolveReflectionTrait(ReflectionTrait $classLike, array $resolved): array
    {
        foreach ($classLike->traits() as $trait) {
            $resolved = $this->doResolve($trait, $resolved);
        }

        return $resolved;
    }
}
