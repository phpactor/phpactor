<?php

namespace Phpactor\Extension\PathFinder;

use Phpactor\ClassFileConverter\PathFinder;
use Phpactor\Extension\Extension;
use Phpactor\Extension\ContainerBuilder;
use Phpactor\Extension\Schema;
use Phpactor\Extension\Container;
use Phpactor\Extension\PathFinder\Application\Navigator;

class PathFinderExtension implements Extension
{
    const PATH_FINDER_DESTINATIONS = 'navigator.destinations';
    const NAVIGATOR_AUTOCREATE = 'navigator.autocreate';

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register('path_finder.path_finder', function (Container $container) {
            return PathFinder::fromDestinations($container->getParameter(self::PATH_FINDER_DESTINATIONS));
        });

        $container->register('application.navigator', function (Container $container) {
            return new Navigator(
                $container->get('path_finder.path_finder'),
                $container->get('application.class_new'),
                $container->getParameter(self::NAVIGATOR_AUTOCREATE)
            );
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Schema $schema)
    {
        $schema->setDefaults([
            self::PATH_FINDER_DESTINATIONS => [],
            self::NAVIGATOR_AUTOCREATE => [],
        ]);
    }
}
