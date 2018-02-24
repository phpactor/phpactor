<?php

namespace Phpactor\Container;

use PhpBench\DependencyInjection\Container as BaseContainer;
use Phpactor\Config\ConfigLoader;

class ApplicationContainer extends BaseContainer
{
    private $configLoader;

    final public function __construct(array $config = [])
    {
        $this->configLoader = new ConfigLoader();

        parent::__construct([
            CodeTransformExtension::class,
            CoreExtension::class,
            RpcExtension::class,
            SourceCodeFilesystemExtension::class,
            WorseReflectionExtension::class,
            PathFinderExtension::class,
        ], array_merge($this->configLoader->loadConfig(), $config));
    }

    public function configLoader(): ConfigLoader
    {
        return $this->configLoader;
    }
}
