<?php

namespace Phpactor\ClassMover\Extension;

use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\ClassMover\Adapter\TolerantParser\TolerantClassFinder;
use Phpactor\ClassMover\Adapter\TolerantParser\TolerantClassReplacer;
use Phpactor\ClassMover\ClassMover;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;
use Phpactor\Container\Container;

class ClassMoverExtension implements Extension
{
    public function configure(Resolver $schema): void
    {
    }

    
    public function load(ContainerBuilder $container): void
    {
        $this->registerClassMover($container);
    }

    private function registerClassMover(ContainerBuilder $container): void
    {
        $container->register(ClassMover::class, function (Container $container) {
            return new ClassMover(
                $container->get('class_mover.class_finder'),
                $container->get('class_mover.ref_replacer')
            );
        });

        $container->register('class_mover.class_finder', function (Container $container) {
            return new TolerantClassFinder();
        });

        $container->register('class_mover.ref_replacer', function (Container $container) {
            return new TolerantClassReplacer($container->get(Updater::class));
        });
    }
}
