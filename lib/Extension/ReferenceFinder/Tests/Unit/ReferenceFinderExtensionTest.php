<?php

namespace Phpactor\Extension\ReferenceFinder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\Extension\ReferenceFinder\Tests\Example\SomeDefinitionLocator;
use Phpactor\Extension\ReferenceFinder\Tests\Example\SomeExtension;
use Phpactor\Extension\ReferenceFinder\Tests\Example\SomeTypeLocator;
use Phpactor\ReferenceFinder\ChainDefinitionLocationProvider;
use Phpactor\ReferenceFinder\ChainTypeLocator;
use Phpactor\ReferenceFinder\ClassImplementationFinder;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Tests\Unit\LocationAssertions;
use Phpactor\TextDocument\TextDocumentBuilder;

class ReferenceFinderExtensionTest extends TestCase
{
    use LocationAssertions;

    public function testEmptyChainDefinitionLocator(): void
    {
        $container = PhpactorContainer::fromExtensions([
            ReferenceFinderExtension::class,
            LoggingExtension::class,
        ]);

        $locator = $container->get(ReferenceFinderExtension::SERVICE_DEFINITION_LOCATOR);
        $this->assertInstanceOf(ChainDefinitionLocationProvider::class, $locator);
    }

    public function testEmptyChainTypeLocator(): void
    {
        $container = PhpactorContainer::fromExtensions([
            ReferenceFinderExtension::class,
            LoggingExtension::class,
        ]);

        $locator = $container->get(ReferenceFinderExtension::SERVICE_TYPE_LOCATOR);
        $this->assertInstanceOf(ChainTypeLocator::class, $locator);
    }

    public function testChainDefinitionLocatorLocatorWithRegisteredLocators(): void
    {
        $container = PhpactorContainer::fromExtensions([
            ReferenceFinderExtension::class,
            SomeExtension::class,
            LoggingExtension::class,
        ]);

        $locator = $container->get(ReferenceFinderExtension::SERVICE_DEFINITION_LOCATOR);
        assert($locator instanceof DefinitionLocator);
        $this->assertInstanceOf(ChainDefinitionLocationProvider::class, $locator);

        $location = $locator->locateDefinition(TextDocumentBuilder::create('asd')->build(), ByteOffset::fromInt(1));

        $this->assertLocation(
            $location->first()->location(),
            SomeDefinitionLocator::EXAMPLE_PATH,
            SomeDefinitionLocator::EXAMPLE_OFFSET,
            SomeDefinitionLocator::EXAMPLE_OFFSET_END
        );
    }

    public function testChainLocatorLocatorWithRegisteredLocators(): void
    {
        $container = PhpactorContainer::fromExtensions([
            ReferenceFinderExtension::class,
            SomeExtension::class,
            LoggingExtension::class,
        ]);

        $locator = $container->get(ReferenceFinderExtension::SERVICE_TYPE_LOCATOR);
        $this->assertInstanceOf(ChainTypeLocator::class, $locator);

        $location = $locator->locateTypes(TextDocumentBuilder::create('asd')->build(), ByteOffset::fromInt(1));

        $this->assertLocation(
            $location->first()->location(),
            SomeTypeLocator::EXAMPLE_PATH,
            SomeTypeLocator::EXAMPLE_OFFSET,
            SomeTypeLocator::EXAMPLE_OFFSET_END
        );
    }

    public function testReturnsImplementationFinder(): void
    {
        $container = PhpactorContainer::fromExtensions([
            ReferenceFinderExtension::class,
            SomeExtension::class,
            LoggingExtension::class,
        ]);

        $finder = $container->get(ReferenceFinderExtension::SERVICE_IMPLEMENTATION_FINDER);
        $this->assertInstanceOf(ClassImplementationFinder::class, $finder);
    }

    public function testReturnsReferenceFinder(): void
    {
        $container = PhpactorContainer::fromExtensions([
            ReferenceFinderExtension::class,
            SomeExtension::class,
            LoggingExtension::class,
        ]);

        $finder = $container->get(ReferenceFinder::class);
        $this->assertInstanceOf(ReferenceFinder::class, $finder);
    }
}
