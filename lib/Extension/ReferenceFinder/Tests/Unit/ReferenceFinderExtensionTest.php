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
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

class ReferenceFinderExtensionTest extends TestCase
{
    public function testEmptyChainDefinitionLocator(): void
    {
        $container = PhpactorContainer::fromExtensions([
            ReferenceFinderExtension::class,
            LoggingExtension::class,
        ]);

        $locator = $container->get(ReferenceFinderExtension::SERVICE_DEFINITION_LOCATOR);
        $this->assertInstanceOf(ChainDefinitionLocationProvider::class, $locator);
    }

    public function testChainDefinitionLocatorLocatorWithRegisteredLocators(): void
    {
        $container = PhpactorContainer::fromExtensions([
            ReferenceFinderExtension::class,
            SomeExtension::class,
            LoggingExtension::class,
        ]);

        $locator = $container->get(ReferenceFinderExtension::SERVICE_DEFINITION_LOCATOR);
        $this->assertInstanceOf(ChainDefinitionLocationProvider::class, $locator);

        $location = $locator->locateDefinition(TextDocumentBuilder::create('asd')->build(), ByteOffset::fromInt(1));
        $this->assertEquals(SomeDefinitionLocator::EXAMPLE_OFFSET, $location->offset()->toInt());
        $this->assertEquals(SomeDefinitionLocator::EXAMPLE_PATH, $location->uri()->path());
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

    public function testChainLocatorLocatorWithRegisteredLocators(): void
    {
        $container = PhpactorContainer::fromExtensions([
            ReferenceFinderExtension::class,
            SomeExtension::class,
            LoggingExtension::class,
        ]);

        $locator = $container->get(ReferenceFinderExtension::SERVICE_TYPE_LOCATOR);
        $this->assertInstanceOf(ChainTypeLocator::class, $locator);

        $location = $locator->locateType(TextDocumentBuilder::create('asd')->build(), ByteOffset::fromInt(1));
        $this->assertEquals(SomeTypeLocator::EXAMPLE_OFFSET, $location->offset()->toInt());
        $this->assertEquals(SomeTypeLocator::EXAMPLE_PATH, $location->uri()->path());
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
