<?php

namespace Phpactor\Extension\ReferenceFinder;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\ReferenceFinder\ChainDefinitionLocationProvider;
use Phpactor\ReferenceFinder\ChainImplementationFinder;
use Phpactor\ReferenceFinder\ChainReferenceFinder;
use Phpactor\ReferenceFinder\ChainTypeLocator;
use Phpactor\ReferenceFinder\NameSearcher;
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\ReferenceFinder\Search\NullNameSearcher;
use Phpactor\ReferenceFinder\TypeLocator;

class ReferenceFinderExtension implements Extension
{
    const SERVICE_DEFINITION_LOCATOR = 'reference_finder.definition_locator';
    const SERVICE_IMPLEMENTATION_FINDER = 'reference_finder.implementation_finder';
    const SERVICE_TYPE_LOCATOR = self::TAG_TYPE_LOCATOR;
    const TAG_DEFINITION_LOCATOR = 'reference_finder.definition_locator';
    const TAG_IMPLEMENTATION_FINDER = 'reference_finder.implementation_finder';
    const TAG_TYPE_LOCATOR = 'reference_finder.type_locator';
    const TAG_REFERENCE_FINDER = 'reference_finder.reference_finder';
    const TAG_NAME_SEARCHER = 'reference_finder.name_searcher';


    public function load(ContainerBuilder $container): void
    {
        $container->register(self::SERVICE_DEFINITION_LOCATOR, function (Container $container) {
            $locators = [];
            foreach (array_keys($container->getServiceIdsForTag(self::TAG_DEFINITION_LOCATOR)) as $serviceId) {
                $locator = $container->get($serviceId);
                if (null === $locator) {
                    continue;
                }
                $locators[] = $locator;
            }

            return new ChainDefinitionLocationProvider($locators, LoggingExtension::channelLogger($container, 'LSP-REF'));
        });

        $container->register(self::SERVICE_TYPE_LOCATOR, function (Container $container) {
            /** @var list<TypeLocator> $locators */
            $locators = [];
            foreach (array_keys($container->getServiceIdsForTag(self::TAG_TYPE_LOCATOR)) as $serviceId) {
                $locators[] = $container->expect($serviceId, TypeLocator::class);
            }

            return new ChainTypeLocator($locators, LoggingExtension::channelLogger($container, 'LSP-REF'));
        });

        $container->register(self::SERVICE_IMPLEMENTATION_FINDER, function (Container $container) {
            $finders = [];
            foreach (array_keys($container->getServiceIdsForTag(self::TAG_IMPLEMENTATION_FINDER)) as $serviceId) {
                $finders[] = $container->get($serviceId);
            }

            return new ChainImplementationFinder($finders);
        });

        $container->register(ReferenceFinder::class, function (Container $container) {
            $finders = [];
            foreach (array_keys($container->getServiceIdsForTag(self::TAG_REFERENCE_FINDER)) as $serviceId) {
                $finders[] = $container->get($serviceId);
            }

            return new ChainReferenceFinder($finders);
        });

        $container->register(NameSearcher::class, function (Container $container) {
            foreach (array_keys($container->getServiceIdsForTag(self::TAG_NAME_SEARCHER)) as $serviceId) {
                return $container->get($serviceId);
            }

            return new NullNameSearcher();
        });
    }


    public function configure(Resolver $schema): void
    {
    }
}
