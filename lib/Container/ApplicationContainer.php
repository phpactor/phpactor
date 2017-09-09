<?php

namespace Phpactor\Container;

use PhpBench\DependencyInjection\Container as BaseContainer;
use Phpactor\Config\ConfigLoader;
use Phpactor\Container\RpcExtension;

class ApplicationContainer extends BaseContainer
{
    private $configLoader;

    final public function __construct()
    {
        $this->configLoader = new ConfigLoader();

        parent::__construct([
            CodeTransformExtension::class,
            CoreExtension::class,
            RpcExtension::class,
            SourceCodeFilesystemExtension::class,
            WorseReflectionExtension::class,
        ], $this->configLoader->loadConfig());
    }

    public function configLoader(): ConfigLoader
    {
        return $this->configLoader;
    }
}
