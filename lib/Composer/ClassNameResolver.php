<?php

declare(strict_types=1);

namespace Phpactor\Composer;

use Phpactor\Generation\SnippetGeneratorInterface;
use Composer\Autoload\ClassLoader;
use Phpactor\CodeContext;
use Phpactor\Composer\ClassFqn;

class ClassNameResolver
{
    /**
     * @var ClassLoader
     */
    private $classLoader;

    public function __construct(ClassLoader $classLoader)
    {
        $this->classLoader = $classLoader;
    }

    public function resolve(string $filePath): ClassFqn
    {
        $prefixes = array_merge(
            $this->classLoader->getPrefixes(),
            $this->classLoader->getPrefixesPsr4(),
            $this->classLoader->getClassMap()
        );

        $map = [];

        if (substr($filePath, 0, 1) === '/') {
            throw new \InvalidArgumentException(sprintf(
                'Do not support absolute paths.'
            ));
        }

        $cwd = getcwd() . '/';

        $bestLength = $base = $basePath = null;
        $isExact = false;

        foreach ($prefixes as $prefix => $files) {
            if (is_string($files)) {
                $files = [ $files ];
            }

            foreach ($files as $file) {
                $path = str_replace($cwd, '', realpath($file));

                if (strpos($filePath, $path) === 0) {
                    if (null !== $bestLength && strlen($path) < $bestLength) {
                        continue;
                    }

                    $base = $prefix;
                    $basePath = $path;
                    $bestLength = strlen($path);

                    if ($filePath === $path) {
                        $isExact = true;
                        break 2; // we are done here
                    }
                }
            }
        }

        if (null === $base) {
            throw new \RuntimeException(sprintf(
                'Could not resolve base path from Composer autoloader'
            ));
        }

        if (false === $isExact && substr($base, -1) !== '\\') {
            $base .= '\\';
        }

        $className = substr($filePath, strlen($basePath) + 1);
        $className = str_replace('/', '\\', $className);
        $className = $base . $className;
        $className = preg_replace('{\.(.+)$}', '', $className);

        return ClassFqn::fromString($className);
    }
}
