<?php

namespace Phpactor\Extension\ReferenceFinder\Tests\Example;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\MapResolver\Resolver;

class SomeExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register('some_definition_locator', function (Container $container) {
            return new SomeDefinitionLocator();
        }, [ ReferenceFinderExtension::TAG_DEFINITION_LOCATOR => []]);
        $container->register('some_type_locator', function (Container $container) {
            return new SomeTypeLocator();
        }, [ ReferenceFinderExtension::TAG_TYPE_LOCATOR => []]);

        $container->register('some_implementation_finder', function (Container $container) {
            return new SomeImplementationFinder();
        }, [ ReferenceFinderExtension::TAG_IMPLEMENTATION_FINDER=> []]);
    }

    
    public function configure(Resolver $schema): void
    {
    }
}
