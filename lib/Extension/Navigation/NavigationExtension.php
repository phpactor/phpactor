<?php

namespace Phpactor\Extension\Navigation;

use Phpactor\ClassFileConverter\PathFinder;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Container\Schema;
use Phpactor\Container\Container;
use Phpactor\Extension\Navigation\Application\Navigator;
use Phpactor\Extension\Navigation\Navigator\ChainNavigator;
use Phpactor\Extension\Navigation\Handler\NavigateHandler;
use Phpactor\Extension\Navigation\Navigator\PathFinderNavigator;

class NavigationExtension implements Extension
{
    const PATH_FINDER_DESTINATIONS = 'navigator.destinations';
    const NAVIGATOR_AUTOCREATE = 'navigator.autocreate';

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $this->registerPathFinder($container);
        $container->register('navigation.navigator.chain', function (Container $container) {
            $navigators = [];
            foreach ($container->getServiceIdsForTag('navigation.navigator') as $serviceId => $attrs) {
                $navigators[] = $container->get($serviceId);
            }

            return new ChainNavigator($navigators);
        });

        $container->register('navigation.navigator.path_finder', function (Container $container) {
            return new PathFinderNavigator($container->get('navigation.path_finder'));
        }, [ 'navigation.navigator' => [] ]);

        $this->registerRpc($container);
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

    private function registerPathFinder(ContainerBuilder $container)
    {
        $container->register('navigation.path_finder', function (Container $container) {
            return PathFinder::fromDestinations($container->getParameter(self::PATH_FINDER_DESTINATIONS));
        });
        
        $container->register('application.navigator', function (Container $container) {
            return new Navigator(
                $container->get('navigation.navigator.chain'),
                $container->get('application.class_new'),
                $container->getParameter(self::NAVIGATOR_AUTOCREATE)
            );
        });
    }

    private function registerRpc(ContainerBuilder $container)
    {
        $container->register('rpc.handler.navigate', function (Container $container) {
            return new NavigateHandler(
                $container->get('application.navigator')
            );
        }, [ 'rpc.handler' => [] ]);
    }
}
