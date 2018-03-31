<?php

namespace Phpactor\Container;

use PhpBench\DependencyInjection\Container as BaseContainer;
use Phpactor\Config\ConfigLoader;
use Phpactor\Config\Paths;

class ApplicationContainer extends BaseContainer
{
    final public function __construct(array $config = [])
    {
        $paths = new Paths();
        $configLoader = new ConfigLoader($paths);

        $this->set('config.paths', $paths);

        parent::__construct([
            CodeTransformExtension::class,
            CoreExtension::class,
            RpcExtension::class,
            SourceCodeFilesystemExtension::class,
            WorseReflectionExtension::class,
            PathFinderExtension::class,
        ], array_merge($configLoader->loadConfig(), $config));
    }
}
