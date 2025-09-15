<?php

namespace Phpactor\Extension\LanguageServerCompletion\Tests;

use PHPUnit\Framework\TestCase;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\CodeTransform\CodeTransformExtension;
use Phpactor\Extension\CompletionWorse\CompletionWorseExtension;
use Phpactor\Extension\Completion\CompletionExtension;
use Phpactor\Extension\ComposerAutoloader\ComposerAutoloaderExtension;
use Phpactor\Extension\LanguageServerBridge\LanguageServerBridgeExtension;
use Phpactor\Extension\LanguageServerCodeTransform\LanguageServerCodeTransformExtension;
use Phpactor\Extension\LanguageServerCompletion\LanguageServerCompletionExtension;
use Phpactor\Extension\LanguageServerCompletion\Tests\Extension\TestExtension;
use Phpactor\Extension\LanguageServerHover\LanguageServerHoverExtension;
use Phpactor\Extension\LanguageServerWorseReflection\LanguageServerWorseReflectionExtension;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\ObjectRenderer\ObjectRendererExtension;
use Phpactor\Extension\Php\PhpExtension;
use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Indexer\Extension\IndexerExtension;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\TestUtils\Workspace;

class IntegrationTestCase extends TestCase
{
    protected function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/Workspace');
    }

    protected function createTester(): LanguageServerTester
    {
        $this->workspace()->reset();
        $container = PhpactorContainer::fromExtensions([
            LoggingExtension::class,
            CompletionExtension::class,
            LanguageServerExtension::class,
            LanguageServerCodeTransformExtension::class,
            LanguageServerCompletionExtension::class,
            FilePathResolverExtension::class,
            ClassToFileExtension::class,
            ComposerAutoloaderExtension::class,

            CodeTransformExtension::class,
            WorseReflectionExtension::class,
            CompletionWorseExtension::class,
            SourceCodeFilesystemExtension::class,
            LanguageServerWorseReflectionExtension::class,
            LanguageServerHoverExtension::class,
            PhpExtension::class,
            TestExtension::class,
            IndexerExtension::class,
            ReferenceFinderExtension::class,

            LanguageServerBridgeExtension::class,
            ObjectRendererExtension::class,
        ], [
            FilePathResolverExtension::PARAM_APPLICATION_ROOT => __DIR__ .'/../../../../',
            ObjectRendererExtension::PARAM_TEMPLATE_PATHS => [],
            IndexerExtension::PARAM_ENABLED_WATCHERS => [],
            LanguageServerExtension::PARAM_DIAGNOSTIC_OUTSOURCE => false,
            LanguageServerExtension::PARAM_ENABLE_TRUST_CHECK => false,
        ]);

        $builder = $container->get(LanguageServerBuilder::class);
        $this->assertInstanceOf(LanguageServerBuilder::class, $builder);

        return $builder->tester(ProtocolFactory::initializeParams($this->workspace()->path('/')));
    }
}
