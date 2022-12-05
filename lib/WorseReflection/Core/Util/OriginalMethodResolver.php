<?php

namespace Phpactor\WorseReflection\Core\Util;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;

class OriginalMethodResolver
{
    public function resolveOriginalMember(ReflectionMember $method): ReflectionMember
    {
        $classLike = $method->declaringClass();
        return $this->doResolveOriginalMember($classLike, $method);
    }

    private function doResolveOriginalMember(
        ReflectionClassLike $classLike,
        ReflectionMember $member
    ): ReflectionMember {
        $members = $classLike->members()->byMemberType($member->memberType());

        if ($members->has($member->name())) {
            $member = $members->get($member->name());
        }

        if ($classLike instanceof ReflectionClass) {
            return $this->resolveClass($classLike, $member);
        }

        if ($classLike instanceof ReflectionInterface) {
            return $this->resolveInterface($classLike, $member);
        }

        return $member;
    }

    private function resolveClass(ReflectionClass $classLike, ReflectionMember $member): ReflectionMember
    {
        $parent = $classLike->parent();

        if ($parent !== null) {
            $member = $this->doResolveOriginalMember(
                $classLike->parent(),
                $member
            );
        }

        foreach ($classLike->interfaces() as $interface) {
            $member = $this->doResolveOriginalMember(
                $interface,
                $member
            );
        }

        return $member;
    }

    private function resolveInterface(ReflectionInterface $classLike, ReflectionMember $member): ReflectionMember
    {
        foreach ($classLike->parents() as $parent) {
            $member = $this->doResolveOriginalMember(
                $parent,
                $member
            );
        }

        return $member;
    }
}
