<?php

namespace Phpactor\Util;

use BetterReflection\Reflector\ClassReflector;
use BetterReflection\SourceLocator\Type\SingleFileSourceLocator;

class ClassUtil
{
    public static function getClassNameFromFile(string $file): string
    {
        return $this->getClassNamesFromFile()->first();
    }

    public static function getClassNamesFromFile(string $file): \Generator
    {
        $reflector = new ClassReflector(new SingleFileSourceLocator($file));

        $classes = $reflector->getAllClasses();

        if (empty($classes)) {
            return;
        }

        foreach ($classes as $class) {
            yield($class->getName());
        }
    }
}
