<?php

namespace Phpactor\Extension\WorseReflectionAnalyse\Tests\Command;

use PHPUnit\Framework\TestCase;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\ComposerAutoloader\ComposerAutoloaderExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Extension\WorseReflectionAnalyse\Command\AnalyseCommand;
use Phpactor\Extension\WorseReflectionAnalyse\WorseReflectionAnalyseExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class AnalyseCommandTest extends TestCase
{
    public function testCommand(): void
    {
        $container = PhpactorContainer::fromExtensions([
            WorseReflectionExtension::class,
            WorseReflectionAnalyseExtension::class,
            SourceCodeFilesystemExtension::class,
            FilePathResolverExtension::class,
            LoggingExtension::class,
            ComposerAutoloaderExtension::class,
            ClassToFileExtension::class,
        ], [
            'file_path_resolver.application_root' => __DIR__ . '/../../../../..',
        ]);
        $command = $container->get(AnalyseCommand::class);
        assert($command instanceof AnalyseCommand);

        $input = new ArrayInput([
            'path' => __FILE__,
        ]);
        $output = new BufferedOutput();
        $exitCode = $command->run($input, $output);
        self::assertEquals(0, $exitCode);
    }
}
