<?php

namespace Phpactor\Generation\Snippet;

use Phpactor\Generation\SnippetGeneratorInterface;
use Composer\Autoload\ClassLoader;
use Phpactor\CodeContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClassGenerator implements SnippetGeneratorInterface
{
    /**
     * @var ClassLoader
     */
    private $classLoader;

    public function __construct(ClassLoader $classLoader)
    {
        $this->classLoader = $classLoader;
    }

    public function generate(CodeContext $codeContext, array $options): string
    {
        $prefixes = array_merge(
            $this->classLoader->getPrefixes(),
            $this->classLoader->getPrefixesPsr4(),
            $this->classLoader->getClassMap()
        );

        $map = [];

        $contextPath = $codeContext->getPath();

        if (substr($contextPath, 0, 1) === '/') {
            throw new \InvalidArgumentException(sprintf(
                'Do not support absolute paths'
            ));
        }

        $cwd = getcwd() . '/';

        $bestLength = $base = $basePath = null;
        foreach ($prefixes as $prefix => $files) {
            if (is_string($files)) {
                $files = [ $files ];
            }

            foreach ($files as $file) {
                $path = str_replace($cwd, '', realpath($file));

                if (strpos($contextPath, $path) === 0) {
                    if (null !== $bestLength && strlen($path) < $bestLength) {
                        continue;
                    }

                    $base = $prefix;
                    $basePath = $path;
                    $bestLength = strlen($path);
                }
            }
        }

        $className = substr($contextPath, strlen($basePath) + 1);
        $className = str_replace('/', '\\', $className);
        $className = $base . $className;
        $className = preg_replace('{\.(.+)$}', '', $className);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }
}
