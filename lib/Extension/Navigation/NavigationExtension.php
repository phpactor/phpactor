<?php

namespace Phpactor\Extension\Navigation;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\Navigation\Application\Navigator;
use Phpactor\Extension\Navigation\Handler\NavigateHandler;
use Phpactor\Extension\Navigation\Navigator\ChainNavigator;
use Phpactor\Extension\Navigation\Navigator\PathFinderNavigator;
use Phpactor\Extension\Navigation\Navigator\WorseReflectionNavigator;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\FilePathResolver\PathResolver;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\MapResolver\Resolver;
use Phpactor\PathFinder\PathFinder;

class NavigationExtension implements Extension
{
    const PATH_FINDER_DESTINATIONS = 'navigator.destinations';
    const NAVIGATOR_AUTOCREATE = 'navigator.autocreate';
    const SERVICE_PATH_FINDER = 'navigation.path_finder';


    public function load(ContainerBuilder $container): void
    {
        $this->registerPathFinder($container);
        $this->registerNavigators($container);
        $this->registerRpc($container);
    }


    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PATH_FINDER_DESTINATIONS => [],
            self::NAVIGATOR_AUTOCREATE => [],
        ]);
    }

    private function registerPathFinder(ContainerBuilder $container): void
    {
        $container->register(self::SERVICE_PATH_FINDER, function (Container $container) {
            return PathFinder::fromAbsoluteDestinations($container->parameter(FilePathResolverExtension::PARAM_PROJECT_ROOT)->string(), $container->parameter(self::PATH_FINDER_DESTINATIONS)->value());
        });

        $container->register('application.navigator', function (Container $container) {
            return new Navigator(
                $container->get('navigation.navigator.chain'),
                $container->get('application.class_new'),
                $container->parameter(self::NAVIGATOR_AUTOCREATE)->value(),
                FilePath::fromString($this->projectRoot($container))
            );
        });
    }

    private function projectRoot(Container $container): string
    {
        /** @var PathResolver $resolver */
        $resolver = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER);
        return $resolver->resolve($container->parameter(SourceCodeFilesystemExtension::PARAM_PROJECT_ROOT)->string());
    }

    private function registerRpc(ContainerBuilder $container): void
    {
        $container->register('rpc.handler.navigate', function (Container $container) {
            return new NavigateHandler(
                $container->get('application.navigator')
            );
        }, [ 'rpc.handler' => ['name' => NavigateHandler::NAME] ]);
    }

    private function registerNavigators(ContainerBuilder $container): void
    {
        $container->register('navigation.navigator.chain', function (Container $container) {
            $navigators = [];
            foreach ($container->getServiceIdsForTag('navigation.navigator') as $serviceId => $attrs) {
                $navigators[] = $container->get($serviceId);
            }

            return new ChainNavigator($navigators);
        });
        $container->register('navigation.navigator.path_finder', function (Container $container) {
            return new PathFinderNavigator($container->get(self::SERVICE_PATH_FINDER));
        }, [ 'navigation.navigator' => [] ]);

        $container->register('navigation.navigator.worse_reflection', function (Container $container) {
            return new WorseReflectionNavigator($container->get(WorseReflectionExtension::SERVICE_REFLECTOR));
        }, [ 'navigation.navigator' => [] ]);
    }
}
