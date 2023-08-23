<?php

namespace Phpactor\Extension\WorseReferenceFinder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\Container\Container;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\ComposerAutoloader\ComposerAutoloaderExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\Extension\WorseReferenceFinder\WorseReferenceFinderExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\ReferenceFinder\TypeLocator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

class WorseReferenceFinderExtensionTest extends TestCase
{
    public function testLocateDefinition(): void
    {
        $container = $this->createContainer();
        $locator = $container->get(ReferenceFinderExtension::SERVICE_DEFINITION_LOCATOR);

        assert($locator instanceof DefinitionLocator);

        $location = $locator->locateDefinition(
            TextDocumentBuilder::create(WorseReferenceFinderExtension::class)->build(),
            ByteOffset::fromInt(3)
        )->first()->range();

        $this->assertEquals(realpath(__DIR__ . '/../../WorseReferenceFinderExtension.php'), $location->uri()->path());
    }

    public function testLocateType(): void
    {
        $container = $this->createContainer();
        $locator = $container->get(ReferenceFinderExtension::SERVICE_TYPE_LOCATOR);

        assert($locator instanceof TypeLocator);

        $location = $locator->locateTypes(
            TextDocumentBuilder::create(
                <<<'EOT'
                    <?php class Foobar{};
                    EOT
            )->language('php')->uri('/foo')->build(),
            ByteOffset::fromInt(10)
        );

        $this->assertEquals('/foo', $location->first()->range()->uri()->path());
    }

    public function testLocateVariable(): void
    {
        $container = $this->createContainer();
        $locator = $container->get(ReferenceFinder::class);

        assert($locator instanceof ReferenceFinder);

        $location = $locator->findReferences(
            TextDocumentBuilder::create(
                <<<'EOT'
                    <?php $var1 = 2; $var1++;
                    EOT
            )->language('php')->uri('/foo')->build(),
            ByteOffset::fromInt(10)
        );

        $this->assertEquals(1, count(iterator_to_array($location)));
    }

    private function createContainer(): Container
    {
        $container = PhpactorContainer::fromExtensions([
            WorseReferenceFinderExtension::class,
            WorseReflectionExtension::class,
            ReferenceFinderExtension::class,
            FilePathResolverExtension::class,
            ClassToFileExtension::class,
            ComposerAutoloaderExtension::class,
            LoggingExtension::class,
        ], [
            'file_path_resolver.application_root' => __DIR__ . '/../../../../../',
        ]);
        return $container;
    }
}
