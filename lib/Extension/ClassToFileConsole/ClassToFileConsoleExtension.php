<?php

namespace Phpactor\Extension\ClassToFileConsole;

use Phpactor\Container\Extension;
use Phpactor\Container\ContainerBuilder;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\ClassToFileConsole\Command\FileInfoCommand;
use Phpactor\ClassFileConverter\Domain\ClassToFileFileToClass;
use Phpactor\ClassFileConverter\Adapter\Composer\ComposerClassToFile;
use Phpactor\ClassFileConverter\Adapter\Simple\SimpleClassToFile;
use Phpactor\ClassFileConverter\Domain\ChainClassToFile;
use Phpactor\ClassFileConverter\Adapter\Composer\ComposerFileToClass;
use Phpactor\ClassFileConverter\Adapter\Simple\SimpleFileToClass;
use Phpactor\Extension\Core\CoreExtension;
use Phpactor\Container\Container;
use Phpactor\Extension\ClassToFileConsole\Application\FileInfo;
use Phpactor\ClassFileConverter\Domain\ChainFileToClass;

class ClassToFileConsoleExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register('command.file_info', function (Container $container) {
            return new FileInfoCommand(
                $container->get('application.file_info'),
                $container->get('console.dumper_registry')
            );
        }, [ 'ui.console.command' => [ 'name' => 'file:info' ]]);

        $container->register('application.file_info', function (Container $container) {
            return new FileInfo(
                $container->get('class_to_file.converter'),
                $container->get('source_code_filesystem.simple')
            );
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
