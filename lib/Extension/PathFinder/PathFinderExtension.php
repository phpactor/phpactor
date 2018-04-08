<?php

namespace Phpactor\Extension\PathFinder;

use Phpactor\ClassFileConverter\PathFinder;
use Phpactor\Extension\Extension;
use Phpactor\Extension\ContainerBuilder;
use Phpactor\Extension\Schema;
use Phpactor\Extension\Container;

class PathFinderExtension implements Extension
{
    const PATH_FINDER_DESTINATIONS = 'navigator.destinations';

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register('path_finder.path_finder', function (Container $container) {
            return PathFinder::fromDestinations($container->getParameter(self::PATH_FINDER_DESTINATIONS));
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Schema $schema)
    {
        $schema->setDefaults([
            self::PATH_FINDER_DESTINATIONS => [],
        ]);
    }
}
