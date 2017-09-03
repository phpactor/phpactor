<?php

namespace Phpactor\Container;

use PhpBench\DependencyInjection\Container as BaseContainer;
use Phpactor\Config\ConfigLoader;
use Phpactor\Container\OffsetActionExtension;

class ApplicationContainer extends BaseContainer
{
    private $configLoader;

    final public function __construct()
    {
        $this->configLoader = new ConfigLoader();

        parent::__construct([
            CoreExtension::class,
            CodeTransformExtension::class,
            SourceCodeFilesystemExtension::class,
            OffsetActionExtension::class,
            WorseReflectionExtension::class,
        ], $this->configLoader->loadConfig());
    }

    public function configLoader(): ConfigLoader
    {
        return $this->configLoader;
    }
}
