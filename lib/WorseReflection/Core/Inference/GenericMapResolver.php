<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\TemplateMap;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;

class GenericMapResolver
{
    private ClassReflector $reflector;

    public function __construct(ClassReflector $reflector)
    {
        $this->reflector = $reflector;
    }

    /**
     * @param Type[] $arguments
     */
    public function resolveClassTemplateMap(Type $topClass, ClassName $bottomClass, array $arguments = []): ?TemplateMap
    {
        if (!$topClass instanceof ClassType) {
            return null;
        }

        $topReflection = $this->reflector->reflectClassLike($topClass->name());

        $templateMap = $topReflection->templateMap();
        $templateMap = $templateMap->mapArguments($arguments);

        if ($topClass->name() == $bottomClass) {
            return $templateMap;
        }

        foreach (array_merge(
            $topReflection->docblock()->implements(),
            $topReflection->docblock()->extends()
        ) as $genericClass
        ) {
            if (!$genericClass instanceof GenericClassType) {
                continue;
            }

            $genericClass = $genericClass->map(function (Type $type) use ($templateMap) {
                if ($templateMap->has($type->short())) {
                    return $templateMap->get($type->short());
                }
                return $type;
            });

            if (!$genericClass instanceof GenericClassType) {
                // should not happen
                continue;
            }

            if (null !== $resolved = $this->resolveClassTemplateMap($genericClass, $bottomClass, $genericClass->arguments())) {
                return $resolved;
            }
        }

        return null;
    }
}
