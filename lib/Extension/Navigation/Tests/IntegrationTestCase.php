<?php

namespace Phpactor\Extension\Navigation\Tests;

use PHPUnit\Framework\TestCase;
use Phpactor\Container\Container;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\CodeTransformExtra\CodeTransformExtraExtension;
use Phpactor\Extension\CodeTransform\CodeTransformExtension;
use Phpactor\Extension\ComposerAutoloader\ComposerAutoloaderExtension;
use Phpactor\Extension\Core\CoreExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\Navigation\NavigationExtension;
use Phpactor\Extension\Php\PhpExtension;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\TestUtils\Workspace;

abstract class IntegrationTestCase extends TestCase
{
    protected Workspace $workspace;

    public function setUp(): void
    {
        $this->workspace = $this->workspace();
        $this->workspace->reset();
    }

    /**
     * @param array{
     * 'navigator.destinations': array<string, string>,
     * 'navigator.autocreate': array<string, string>,
     * } $config
     */
    protected function container(array $config): Container
    {
        $key = serialize($config);
        static $container = [];

        if (isset($container[$key])) {
            return $container[$key];
        }

        $container[$key] = PhpactorContainer::fromExtensions([
            CodeTransformExtension::class,
            CodeTransformExtraExtension::class,
            PhpExtension::class,
            CoreExtension::class,
            NavigationExtension::class,
            LoggingExtension::class,
            SourceCodeFilesystemExtension::class,
            ClassToFileExtension::class,
            ComposerAutoloaderExtension::class,
            FilePathResolverExtension::class,
            WorseReflectionExtension::class,
        ], array_merge([
            LoggingExtension::PARAM_ENABLED=> true,
            LoggingExtension::PARAM_PATH=> 'php://stderr',
            WorseReflectionExtension::PARAM_ENABLE_CACHE=> false,
            WorseReflectionExtension::PARAM_STUB_DIR => $this->workspace()->path(),
            FilePathResolverExtension::PARAM_APPLICATION_ROOT => __DIR__ . '/../',
            FilePathResolverExtension::PARAM_PROJECT_ROOT => $this->workspace()->path(),
        ], $config));

        return $container[$key];
    }

    protected function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/Workspace');
    }
}
