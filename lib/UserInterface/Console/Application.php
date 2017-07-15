<?php

namespace Phpactor\UserInterface\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use PhpBench\DependencyInjection\Container;
use Phpactor\Container\CoreExtension;
use XdgBaseDir\Xdg;
use Webmozart\PathUtil\Path;
use Symfony\Component\Yaml\Yaml;
use Phpactor\Container\CodeTransformExtension;
use Phpactor\Phpactor;

class Application extends SymfonyApplication
{
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct('Phpactor', '0.1');
    }

    public function initialize()
    {
        $container = new Container([
            CoreExtension::class,
            CodeTransformExtension::class,
        ], Phpactor::loadConfig());
        $container->init();

        foreach ($container->getServiceIdsForTag('ui.console.command') as $commandId => $attrs) {
            $this->add($container->get($commandId));
        }
    }
}
