<?php

namespace Phpactor\Container;

use PhpBench\DependencyInjection\Container as BaseContainer;
use Phpactor\Config\ConfigLoader;

class ApplicationContainer extends BaseContainer
{
    private $configLoader;

    final public function __construct()
    {
        $this->configLoader = new ConfigLoader();

        parent::__construct([
            CoreExtension::class,
            CodeTransformExtension::class,
        ], $this->configLoader->loadConfig());
    }

    public function configLoader(): ConfigLoader
    {
        return $this->configLoader;
    }
}
