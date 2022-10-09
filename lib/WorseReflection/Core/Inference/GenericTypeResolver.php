<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;

class GenericTypeResolver
{
    public static function declaringClass(ReflectionMember $member): ReflectionClassLike
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
