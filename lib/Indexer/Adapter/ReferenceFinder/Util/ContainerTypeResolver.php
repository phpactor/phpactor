<?php

namespace Phpactor\Indexer\Adapter\ReferenceFinder\Util;

use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;

class ContainerTypeResolver
{
    /**
     * @var ClassReflector
     */
    private $reflector;

    public function __construct(ClassReflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function resolveDeclaringContainerType(string $memberType, string $memberName, ?string $containerFqn): ?string
    {
        if (null === $containerFqn) {
            return null;
        }

        try {
            $classLike = $this->reflector->reflectClassLike($containerFqn);
            $members = $classLike->members()->byMemberType($memberType);

            return $members->get($memberName)->original()->declaringClass()->name()->__toString();
        } catch (NotFound $notFound) {
            return $containerFqn;
        }
    }
}
