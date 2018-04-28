<?php

namespace Phpactor\Extension\Navigation\Navigator;

use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Reflector;

class WorseReflectionNavigator implements Navigator
{
    /**
     * @var Reflector
     */
    private $reflector;

    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function destinationsFor(string $path): array
    {
        $destinations = [];
        $source = SourceCode::fromPath($path);
        $classes = $this->reflector->reflectClassesIn($source);

        foreach ($classes as $class) {
            if ($class instanceof ReflectionClass) {
                $destinations = $this->forReflectionClass($destinations, $class);
            }
        }

        return $destinations;
    }

    private function forReflectionClass(array $destinations, ReflectionClass $class)
    {
        $parentClass = $class->parent();
        if ($parentClass instanceof ReflectionClass) {
            $destinations['parent (' . $parentClass->name()->short() . ')'] = $parentClass->sourceCode()->path();
        }

        foreach ($class->interfaces() as $interface) {
            $destinations['interface (' . $interface->name()->short() . ')'] = $interface->sourceCode()->path();
        }

        return $destinations;
    }
}
