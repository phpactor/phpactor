<?php

namespace Phpactor\Util;

use BetterReflection\Reflector\ClassReflector;
use BetterReflection\SourceLocator\Type\SingleFileSourceLocator;

class ClassUtil
{
    public static function getClassNameFromFile(string $file): string
    {
        $reflector = new ClassReflector(new SingleFileSourceLocator($file));

        $classes = $reflector->getAllClasses();

        if (empty($classes)) {
            return;
        }

        $class = reset($classes);

        return $class->getName();
    }
}
