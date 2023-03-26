<?php

namespace Phpactor\Extension\Navigation\Navigator;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Reflector;

class WorseReflectionNavigator implements Navigator
{
    public function __construct(private Reflector $reflector)
    {
    }

    public function destinationsFor(string $path): array
    {
        $destinations = [];
        $source = TextDocument::fromPath($path);
        $classes = $this->reflector->reflectClassesIn($source);

        foreach ($classes as $class) {
            if ($class instanceof ReflectionClass) {
                $destinations = $this->forReflectionClass($destinations, $class);
            }

            if ($class instanceof ReflectionInterface) {
                $destinations = $this->forReflectionInterface($destinations, $class);
            }
        }

        return $destinations;
    }

    private function forReflectionClass(array $destinations, ReflectionClass $class)
    {
        $parentClass = $class->parent();
        if ($parentClass instanceof ReflectionClass) {
            $destinations['parent'] = $parentClass->sourceCode()->path();
        }

        foreach ($class->interfaces() as $interface) {
            $destinations['interface:'.$interface->name()->short()] = $interface->sourceCode()->path();
        }

        return $destinations;
    }

    private function forReflectionInterface($destinations, ReflectionInterface $class)
    {
        foreach ($class->parents() as $interface) {
            $destinations['interface:'.$interface->name()->short()] = $interface->sourceCode()->path();
        }

        return $destinations;
    }
}
