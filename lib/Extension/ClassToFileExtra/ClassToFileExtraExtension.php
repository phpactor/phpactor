<?php

namespace Phpactor\Extension\ClassToFileExtra;

use Phpactor\Container\Extension;
use Phpactor\Container\ContainerBuilder;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\ClassToFileExtra\Command\FileInfoCommand;
use Phpactor\Container\Container;
use Phpactor\Extension\ClassToFileExtra\Application\FileInfo;

class ClassToFileExtraExtension implements Extension
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
