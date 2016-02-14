<?php

namespace PhpActor\Knowledge\Reflection;

interface ReflectorInterface
{
    /**
     * Reflect a class and return a class reflection hierarchy
     */
    public function reflect(string $file, string $bootstrap = null): ClassHierarchy;
}
