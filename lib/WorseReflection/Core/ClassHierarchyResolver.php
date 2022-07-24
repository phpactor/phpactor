<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;

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
}
