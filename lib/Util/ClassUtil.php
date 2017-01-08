<?php

namespace Phpactor\Util;

use BetterReflection\Reflector\ClassReflector;
use BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use BetterReflection\SourceLocator\Type\StringSourceLocator;

class ClassUtil
{
    public function getClassNameFromFile(string $file): string
    {
        $reflector = new ClassReflector(new SingleFileSourceLocator($file));

        $classes = $reflector->getAllClasses();

        if (empty($classes)) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find a class in "%s"', $file
            ));
        }

        $class = reset($classes);

        return $class->getName();
    }

    public function getClassNameFromSource(string $source)
    {
        $reflector = new ClassReflector(new StringSourceLocator($source));

        $classes = $reflector->getAllClasses();

        if (empty($classes)) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find a class in "%s"', $file
            ));
        }

        $class = reset($classes);

        return $class->getName();
    }
}
