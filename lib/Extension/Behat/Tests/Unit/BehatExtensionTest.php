<?php

namespace Phpactor\Extension\Behat\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Behat\BehatExtension;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\ComposerAutoloader\ComposerAutoloaderExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

class BehatExtensionTest extends TestCase
{
    public function testStepDefinitionFinder(): void
    {
        $container = PhpactorContainer::fromExtensions([
            BehatExtension::class,
            ReferenceFinderExtension::class,
            FilePathResolverExtension::class,
            WorseReflectionExtension::class,
            ClassToFileExtension::class,
            ComposerAutoloaderExtension::class,
            LoggingExtension::class,
        ], [
            FilePathResolverExtension::PARAM_APPLICATION_ROOT => __DIR__ . '/../../../../..',
            BehatExtension::PARAM_CONFIG_PATH => __DIR__ .'/../Integration/Completor/behat.yml',
        ]);

        $locator = $container->get(ReferenceFinderExtension::SERVICE_DEFINITION_LOCATOR);
        assert($locator instanceof DefinitionLocator);
        $location = $locator->locateDefinition(
            TextDocumentBuilder::fromUri(__DIR__. '/../Integration/Completor/feature/some_feature.feature')->language('cucumber')->build(),
            ByteOffset::fromInt(69)
        );

        $this->assertStringContainsString('ExampleContext.php', $location->first()->range()->uri()->path());
    }
}
