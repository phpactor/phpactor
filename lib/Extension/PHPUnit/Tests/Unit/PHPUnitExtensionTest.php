<?php

namespace Phpactor\Extension\PHPUnit\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\ComposerAutoloader\ComposerAutoloaderExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\PHPUnit\PHPUnitExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\WorseReflection\Reflector;

class PHPUnitExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $container = PhpactorContainer::fromExtensions([
            WorseReflectionExtension::class,
            PHPUnitExtension::class,
            FilePathResolverExtension::class,
            ClassToFileExtension::class,
            ComposerAutoloaderExtension::class,
            LoggingExtension::class,
        ], [
            FilePathResolverExtension::PARAM_APPLICATION_ROOT => __DIR__
        ]);

        $reflector = $container->get(WorseReflectionExtension::SERVICE_REFLECTOR);
        $this->assertInstanceOf(Reflector::class, $reflector);
    }
}
