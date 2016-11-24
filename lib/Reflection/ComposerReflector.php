<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phpactor\Reflection;

use BetterReflection\Reflector\ClassReflector;
use BetterReflection\SourceLocator\Type\ComposerSourceLocator;
use BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use Composer\Autoload\ClassLoader;
use BetterReflection\Reflection\Reflection;
use BetterReflection\Reflection\ReflectionClass;

class ComposerReflector extends AbstractFileReflector
{
    private $reflector;

    public function __construct(ClassLoader $classLoader)
    {
        $this->reflector = new ClassReflector(new ComposerSourceLocator($classLoader));
    }

    public function reflectFile(string $file): ReflectionClass
    {
        $classFqn = $this->getClassNameFromFile($file);

        if (null === $classFqn) {
            throw new Exception\ReflectionException(sprintf('Could not find a class name in "%s"', $file));
        }

        return $this->reflectClass($classFqn);
    }

    public function reflectClass(string $classFqn): ReflectionClass
    {
        $reflection = $this->reflector->reflect($classFqn);

        if (null === $reflection) {
            $locator = new SingleFileSourceLocator($file);
            $reflector = new ClassReflector($locator);
            $reflection = $reflector->reflect($class);
        }

        if (null === $reflection) {
            throw new \InvalidArgumentException(sprintf(
                'Composer could not find class "%s" for file "%s" and falling back to single-file source location failed.',
                $class, $file
            ));
        }

        return $reflection;
    }
}
