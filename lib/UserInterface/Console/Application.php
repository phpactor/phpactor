<?php

namespace Phpactor\UserInterface\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use PhpBench\DependencyInjection\Container;
use Phpactor\Container\CoreExtension;

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
        ], []);
        $container->init();
        $this->addCommands([
            $container->get('command.move'),
            $container->get('command.offsetinfo'),
        ]);
    }
}
