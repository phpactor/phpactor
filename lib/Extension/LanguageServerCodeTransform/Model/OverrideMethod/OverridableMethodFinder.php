<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Model\OverrideMethod;

use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Reflector;

class OverridableMethodFinder
{
    public function __construct(private Reflector $reflector)
    {
    }
    /**
     * @return ReflectionMethod[]
     */
    public function find(TextDocument $document): array
    {
        $overrideables = [];
        foreach ($this->reflector->reflectClassesIn($document) as $class) {
            if (!$class instanceof ReflectionClass) {
                continue;
            }
            $parent = $class->parent();
            if (!$parent) {
                continue;
            }
            $methods = $parent->methods()->byVisibilities([
                Visibility::protected(),
                Visibility::public(),
            ]);
            if ($methods->count() === 0) {
                continue;
            }

            $ownMethods = $class->methods()->belongingTo($class->name());
            foreach ($methods as $method) {
                if ($ownMethods->has($method->name())) {
                    continue;
                }
                $method = $method->withClass($class);
                if (!$method instanceof ReflectionMethod) {
                    continue;
                }
                $overrideables[] = $method;
            }
        }

        return array_values($overrideables);
    }
}
