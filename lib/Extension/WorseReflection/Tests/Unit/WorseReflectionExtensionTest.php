<?php

namespace Phpactor\Extension\WorseReflection\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\Container\Container;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\ComposerAutoloader\ComposerAutoloaderExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\WorseReflection\Reflector;

class WorseReflectionExtensionTest extends TestCase
{
    public function testProvideReflector(): void
    {
        $reflector = $this->createReflector([
            FilePathResolverExtension::PARAM_APPLICATION_ROOT => __DIR__ . '/../../../../..',
        ]);
        $this->assertEquals((string) $reflector->reflectClass(__CLASS__)->name(), __CLASS__);
    }

    public function testRegistersTaggedFramewalkers(): void
    {
        $reflector = $this->createReflector([
            FilePathResolverExtension::PARAM_APPLICATION_ROOT => __DIR__ . '/../../../../..',
        ]);
        $frame = $reflector->reflectClass(__CLASS__)->methods()->get('testRegistersTaggedFramewalkers')->frame();
        $this->assertCount(1, $frame->locals()->byName('test_variable'));
    }

    public function testProvideReflectorWithStubs(): void
    {
        $reflector = $this->createReflector([
            FilePathResolverExtension::PARAM_APPLICATION_ROOT => __DIR__ . '/../../../../..'
        ]);
        $this->assertEquals((string) $reflector->reflectClass(__CLASS__)->name(), __CLASS__);
    }

    public function testAdditiveStubPaths(): void
    {
        $reflector = $this->createReflector([
            WorseReflectionExtension::PARAM_ADDITIVE_STUBS => [
                'example/stub.stub',
            ],
            FilePathResolverExtension::PARAM_APPLICATION_ROOT => __DIR__ . '/../../../../..',
            FilePathResolverExtension::PARAM_PROJECT_ROOT => __DIR__
        ]);

        $reflection = $reflector->reflectClass(__CLASS__);
        $method = $reflection->methods()->byName('testAdditiveStubPaths')->first();
        self::assertEquals('string', $method->inferredType()->__toString());
    }

    public function testProvideReflectorWithStubsAndCustomCacheDir(): void
    {
        $reflector = $this->createReflector([
            FilePathResolverExtension::PARAM_APPLICATION_ROOT => __DIR__,
            WorseReflectionExtension::PARAM_STUB_DIR => __DIR__ . '/../../../../../vendor/jetbrains/phpstorm-stubs',
            WorseReflectionExtension::PARAM_STUB_CACHE_DIR => $cachePath = __DIR__ . '/../../stubs'
        ]);
        $this->assertEquals((string) $reflector->reflectClass(__CLASS__)->name(), __CLASS__);
        $this->assertFileExists($cachePath);
    }

    /**
     * @param array<string,mixed> $params
     */
    private function createReflector(array $params = []): Reflector
    {
        $container = $this->createContainer($params);

        return $container->get(WorseReflectionExtension::SERVICE_REFLECTOR);
    }

    /**
     * @param array<string,mixed> $params
     */
    private function createContainer(array $params): Container
    {
        return PhpactorContainer::fromExtensions([
            WorseReflectionExtension::class,
            FilePathResolverExtension::class,
            ClassToFileExtension::class,
            ComposerAutoloaderExtension::class,
            LoggingExtension::class,
            TestExtension::class,
        ], $params);
    }
}
