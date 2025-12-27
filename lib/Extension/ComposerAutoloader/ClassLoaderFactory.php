<?php

namespace Phpactor\Extension\ComposerAutoloader;

use Composer\Autoload\ClassLoader;
use Psr\Log\LoggerInterface;

class ClassLoaderFactory
{
    public function __construct(
        private readonly string $composerDir,
        private readonly LoggerInterface $logger
    ) {
    }

    public function getLoader(): ClassLoader
    {
        $loader = new ClassLoader();

        foreach ($this->resolveMap('autoload_namespaces.php') as $namespace => $path) {
            $loader->set($namespace, $path);
        }

        foreach ($this->resolveMap('autoload_psr4.php') as $namespace => $path) {
            $loader->setPsr4($namespace, $path);
        }

        if ($classMap = $this->resolveMap('autoload_classmap.php')) {
            $loader->addClassMap($classMap);
        }

        return $loader;
    }

    private function resolveMap(string $fileName): array
    {
        $path = $this->composerDir . '/' . $fileName;

        if (!file_exists($path)) {
            $this->logger->warning(sprintf(
                'Composer file "%s" does not exist',
                $path
            ));
            return [];
        }

        $map = require $path;

        if (!is_array($map)) {
            $this->logger->warning(sprintf(
                'Composer map for "%s" is not an array',
                $path
            ));
            return [];
        }

        return $map;
    }
}
