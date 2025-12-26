<?php

namespace Phpactor\Indexer\Adapter\ReferenceFinder\Util;

use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;

class ContainerTypeResolver
{
    public function __construct(private readonly ClassReflector $reflector)
    {
    }

    /**
     * @param ReflectionMember::TYPE_* $memberType
     */
    public function resolveDeclaringContainerType(string $memberType, string $memberName, ?string $containerFqn): ?string
    {
        if (null === $containerFqn) {
            return null;
        }

        try {
            $classLike = $this->reflector->reflectClassLike($containerFqn);
            $members = $classLike->members()->byMemberType($memberType);

            return $members->get($memberName)->original()->declaringClass()->name()->__toString();
        } catch (NotFound) {
            return $containerFqn;
        }
    }

    /**
     * @param ReflectionMember::TYPE_* $memberType
     */
    public function resolveDeclaringClass(string $memberType, string $memberName, ?string $containerFqn): ?string
    {
        if (null === $containerFqn) {
            return null;
        }

        try {
            $classLike = $this->reflector->reflectClassLike($containerFqn);
            $members = $classLike->members()->byMemberType($memberType);

            return $members->get($memberName)->declaringClass()->name()->__toString();
        } catch (NotFound) {
            return $containerFqn;
        }
    }
}
