<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;

final class ClassHierarchyResolver
{
    public const INCLUDE_TRAIT =     0x00000001;
    public const INCLUDE_INTERFACE = 0x00000010;
    public const INCLUDE_PARENT =    0x00000100;
    public const INCLUDE_MIXIN =     0x00001000;

    /**
     * @param int-mask-of<self::INCLUDE_*> $mode
     */
    public function __construct(
        private int $mode = self::INCLUDE_TRAIT | self::INCLUDE_INTERFACE | self::INCLUDE_PARENT | self::INCLUDE_MIXIN
    ) {
    }

    /**
     * @param array<string,ReflectionClassLike> $resolved
     * @return ReflectionClassLike[]
     */
    public function resolve(ReflectionClassLike $classLike, array $resolved = []): array
    {
        return array_reverse($this->doResolve($classLike, $resolved));
    }

    /**
     * @param array<string,ReflectionClassLike> $resolved
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
     * @param array<string,ReflectionClassLike> $resolved
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
     * @param array<string,ReflectionClassLike> $resolved
     * @return ReflectionClassLike[]
     */
    private function resolveReflectionClass(ReflectionClass $classLike, array $resolved): array
    {
        if ($this->mode & self::INCLUDE_PARENT) {
            $parent = $classLike->parent();
            if ($parent) {
                $resolved = $this->doResolve($parent, $resolved);
            }
        }

        if ($this->mode & self::INCLUDE_INTERFACE) {
            foreach ($classLike->interfaces() as $interface) {
                $resolved = $this->doResolve($interface, $resolved);
            }
        }

        if ($this->mode & self::INCLUDE_TRAIT) {
            foreach ($classLike->traits() as $interface) {
                $resolved = $this->doResolve($interface, $resolved);
            }
        }

        if ($this->mode & self::INCLUDE_MIXIN) {
            // consider making this an extension point and "mixins" an extension
            foreach ($classLike->docblock()->mixins() as $mixin) {
                if ($mixin instanceof ReflectedClassType) {
                    $reflection = $mixin->reflectionOrNull();
                    if ($reflection) {
                        $resolved = $this->doResolve($reflection, $resolved);
                    }
                }
            }
        }

        return $resolved;
    }

    /**
     * @param array<string,ReflectionClassLike> $resolved
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
