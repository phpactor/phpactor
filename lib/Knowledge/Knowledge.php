<?php

namespace PhpActor\Knowledge;

use PhpActor\Knowledge\Reflection\ClassHierarchy;
use PhpActor\Knowledge\Storage\Repository;

class Knowledge
{
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Return class metadata for the given class FQN.
     *
     * @param string $classFqn
     *
     * @return ClassHierarchy
     */
    public function getClassHierarchy(string $classFqn): ClassHierarchy
    {
    }

    /**
     * Return an array of class FQNs for the given class short name.  The short
     * name is the actual name of the class within the namespace (e.g.
     * Knowledge).
     *
     * @param string $shortName
     *
     * @return string[]
     */
    public function suggestFromShortName(string $shortName): string
    {
    }
}
