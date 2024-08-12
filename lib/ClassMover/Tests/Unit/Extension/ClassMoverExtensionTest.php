<?php

namespace Phpactor\ClassMover\Tests\Unit\Extension;

use PHPUnit\Framework\TestCase;
use Phpactor\ClassMover\ClassMover;
use Phpactor\ClassMover\Extension\ClassMoverExtension;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\CodeTransform\CodeTransformExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\Php\PhpExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;

class ClassMoverExtensionTest extends TestCase
{
    public function testBoot(): void
    {
        $container = PhpactorContainer::fromExtensions([
            ClassMoverExtension::class,
            CodeTransformExtension::class,
            FilePathResolverExtension::class,
            LoggingExtension::class,
            PhpExtension::class,
            WorseReflectionExtension::class,
        ], [
            FilePathResolverExtension::PARAM_APPLICATION_ROOT => realpath(__DIR__ .'/..'),
            CodeTransformExtension::PARAM_TEMPLATE_PATHS => [],
        ]);
        $var = $container->get(ClassMover::class);
        self::assertInstanceOf(ClassMover::class, $var);
    }
}
