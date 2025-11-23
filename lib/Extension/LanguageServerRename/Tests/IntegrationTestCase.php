<?php

namespace Phpactor\Extension\LanguageServerRename\Tests;

use Phpactor\Extension\LanguageServerBridge\LanguageServerBridgeExtension;
use Phpactor\Extension\LanguageServerRename\LanguageServerRenameExtension;
use Phpactor\Extension\LanguageServerRename\Tests\Extension\TestExtension;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Container\Container;
use PHPUnit\Framework\TestCase;
use Phpactor\TestUtils\Workspace;

class IntegrationTestCase extends TestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    protected function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/Workspace');
    }

    protected function container(array $config = []): Container
    {
        $container = PhpactorContainer::fromExtensions([
            LanguageServerExtension::class,
            TestExtension::class,
            LanguageServerRenameExtension::class,
            FilePathResolverExtension::class,
            LanguageServerBridgeExtension::class,
            LoggingExtension::class,
            ReferenceFinderExtension::class,
        ], array_merge([
            LanguageServerExtension::PARAM_ENABLE_TRUST_CHECK => false,
        ], $config));

        return $container;
    }
}
