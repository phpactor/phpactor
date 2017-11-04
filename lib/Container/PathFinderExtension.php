<?php

namespace Phpactor\Container;

use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\DependencyInjection\Container;
use Phpactor\ClassFileConverter\PathFinder;

class PathFinderExtension implements ExtensionInterface
{
    const PATH_FINDER_DESTINATIONS = 'navigator.destinations';

    /**
     * {@inheritDoc}
     */
    public function load(Container $container)
    {
        $container->register('path_finder.path_finder', function (Container $container) {
            return PathFinder::fromDestinations($container->getParameter(self::PATH_FINDER_DESTINATIONS));
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultConfig()
    {
        return [
            self::PATH_FINDER_DESTINATIONS => [],
        ];
    }
}
