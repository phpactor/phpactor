<?php

namespace Phpactor\Extension\ClassToFile\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\ClassFileConverter\Domain\ClassName;
use Phpactor\ClassFileConverter\Domain\ClassToFileFileToClass;
use Phpactor\Container\Container;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\ComposerAutoloader\ComposerAutoloaderExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;

/**
 * @isolateProcess
 */
class ClassToFileExtensionTest extends TestCase
{
    public function testCreatesConverter(): void
    {
        $converter = $this->createConverter();
        $candidates = $converter->classToFileCandidates(ClassName::fromString(__CLASS__));
        $file = $candidates->best();
        $candidates = $converter->fileToClassCandidates($file);
        $this->assertEquals('ClassToFileExtensionTest', $candidates->best()->name());
    }

    public function testCreatesConverterWithoutComposer(): void
    {
        $converter = $this->createConverter([
            ComposerAutoloaderExtension::PARAM_AUTOLOADER_PATH => __DIR__ . '/autoload.php',
            FilePathResolverExtension::PARAM_PROJECT_ROOT => __DIR__
        ]);
        $candidates = $converter->classToFileCandidates(ClassName::fromString(__CLASS__));
        $this->assertCount(1, $candidates);
        $file = $candidates->best();
        $candidates = $converter->fileToClassCandidates($file);
        $this->assertEquals('ClassToFileExtensionTest', $candidates->best()->name());
    }

    private function create(array $params): Container
    {
        return PhpactorContainer::fromExtensions([
            ClassToFileExtension::class,
            ComposerAutoloaderExtension::class,
            FilePathResolverExtension::class,
            LoggingExtension::class,
        ], $params);
    }

    private function createConverter(array $config = []): ClassToFileFileToClass
    {
        $converter = $this->create($config)->get(ClassToFileExtension::SERVICE_CONVERTER);
        return $converter;
    }
}
