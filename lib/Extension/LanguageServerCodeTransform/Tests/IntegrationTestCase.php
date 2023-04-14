<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Tests;

use PHPUnit\Framework\TestCase;
use Phpactor\Container\Container;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\CodeTransform\CodeTransformExtension;
use Phpactor\Extension\ComposerAutoloader\ComposerAutoloaderExtension;
use Phpactor\Extension\LanguageServerBridge\LanguageServerBridgeExtension;
use Phpactor\Extension\LanguageServerCodeTransform\LanguageServerCodeTransformExtension;
use Phpactor\Extension\LanguageServerIndexer\LanguageServerIndexerExtension;
use Phpactor\Extension\LanguageServerWorseReflection\LanguageServerWorseReflectionExtension;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\Php\PhpExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Indexer\Extension\IndexerExtension;
use Phpactor\TestUtils\Workspace;

class IntegrationTestCase extends TestCase
{
    public function container(array $config = []): Container
    {
        $this->workspace()->put('index/.foo', '');
        $container = PhpactorContainer::fromExtensions([
            LoggingExtension::class,
            LanguageServerExtension::class,
            FilePathResolverExtension::class,
            ClassToFileExtension::class,
            ComposerAutoloaderExtension::class,
            CodeTransformExtension::class,
            LanguageServerCodeTransformExtension::class,
            WorseReflectionExtension::class,
            IndexerExtension::class,
            LanguageServerIndexerExtension::class,
            LanguageServerWorseReflectionExtension::class,
            PhpExtension::class,
            LanguageServerBridgeExtension::class,
            TestLanguageServerSessionExtension::class,
        ], array_merge([
            LanguageServerExtension::PARAM_DIAGNOSTIC_OUTSOURCE => false,
            FilePathResolverExtension::PARAM_APPLICATION_ROOT => __DIR__ .'/../../',
            WorseReflectionExtension::PARAM_STUB_DIR => __DIR__. '/Empty',
            WorseReflectionExtension::PARAM_STUB_CACHE_DIR => __DIR__ . '/Workspace/wr-cache',
            IndexerExtension::PARAM_STUB_PATHS => [__DIR__. '/Stub'],
            CodeTransformExtension::PARAM_TEMPLATE_PATHS => [],
            FilePathResolverExtension::PARAM_PROJECT_ROOT => $this->workspace()->path(),
            IndexerExtension::PARAM_INDEX_PATH => $this->workspace()->path('index'),
            LoggingExtension::PARAM_ENABLED => true,
            IndexerExtension::PARAM_ENABLED_WATCHERS => [],
            LanguageServerExtension::PARAM_DIAGNOSTIC_SLEEP_TIME => 0,
        ], $config));

        return $container;
    }

    protected function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/Workspace');
    }
}
