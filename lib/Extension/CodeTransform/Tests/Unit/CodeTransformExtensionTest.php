<?php

namespace Phpactor\Extension\CodeTransform\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeTransform\Adapter\Native\GenerateNew\ClassGenerator;
use Phpactor\CodeTransform\Domain\ClassName;
use Phpactor\Container\Container;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\CodeTransform\CodeTransformExtension;
use Phpactor\Extension\ComposerAutoloader\ComposerAutoloaderExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\Php\PhpExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;

class CodeTransformExtensionTest extends TestCase
{
    public function testLoadServices(): void
    {
        $container = $this->createContainer();

        foreach ($container->getServiceIds() as $serviceId) {
            $service = $container->get($serviceId);
        }
        $this->addToAssertionCount(1);
    }

    #[DataProvider('provideClassNew')]
    public function testClassNew(string $variant): void
    {
        /** @var array<string,ClassGenerator> */
        $generators = $this->createContainer()->get('code_transform_extra.class_generator.variants');
        self::assertArrayHasKey($variant, $generators);
        $generators[$variant]->generateNew(ClassName::fromString('Foo'));
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideClassNew(): Generator
    {
        yield ['default'];
        yield ['interface'];
        yield ['enum'];
        yield ['trait'];
    }

    private function createContainer(): Container
    {
        $container = PhpactorContainer::fromExtensions([
            CodeTransformExtension::class,
            ClassToFileExtension::class,
            ComposerAutoloaderExtension::class,
            FilePathResolverExtension::class,
            LoggingExtension::class,
            PhpExtension::class,
            WorseReflectionExtension::class,
        ], [
            CodeTransformExtension::PARAM_TEMPLATE_PATHS => [
                __DIR__ . '/../../../../../templates/code',
            ],
            FilePathResolverExtension::PARAM_APPLICATION_ROOT => __DIR__,
        ]);

        return $container;
    }
}
