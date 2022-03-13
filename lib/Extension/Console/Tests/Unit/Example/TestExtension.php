<?php

namespace Phpactor\Extension\Console\Tests\Unit\Example;

use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\MapResolver\Resolver;

class TestExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register('test.command.test', function () {
            return new TestCommand();
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'test' ] ]);
    }

    
    public function configure(Resolver $schema): void
    {
    }
}
