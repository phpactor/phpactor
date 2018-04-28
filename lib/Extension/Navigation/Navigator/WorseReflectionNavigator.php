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
                $parentClass = $class->parent();

                if ($parentClass instanceof ReflectionClass) {
                    $destinations['parent (' . $parentClass->name()->short() . ')'] = $parentClass->sourceCode()->path();
                }
            }
        }

        return $destinations;
    }
}
