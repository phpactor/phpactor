<?php

namespace Phpactor\Extension\DependencyFinder;

use Phpactor\Container\Extension;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Schema;
use Phpactor\Container\Container;
use Phpactor\Extension\DependencyFinder\Command\DependencyFinderCommand;

class DependencyFinderExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register('dependency_finder.dependency_finder', function (Container $container) {
            return new DependencyFinder(
                $container->get('source_code_filesystem.simple'),
                $container->get('reflection.reflector'),
                $container->get('class_to_file.file_to_class')
            );
        });

        $container->register('dependency_finder.command.dependency_finder', function (Container $container) {
            return new DependencyFinderCommand($container->get('dependency_finder.dependency_finder'));
        }, [ 'ui.console.command' => []]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Schema $schema)
    {
    }
}
