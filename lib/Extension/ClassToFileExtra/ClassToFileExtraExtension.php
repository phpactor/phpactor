<?php

namespace Phpactor\Extension\ClassToFileExtra;

use Phpactor\Container\Extension;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Extension\ClassToFileExtra\Rpc\FileInfoHandler;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\ClassToFileExtra\Command\FileInfoCommand;
use Phpactor\Container\Container;
use Phpactor\Extension\ClassToFileExtra\Application\FileInfo;

class ClassToFileExtraExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register('command.file_info', function (Container $container) {
            return new FileInfoCommand(
                $container->get('application.file_info'),
                $container->get('console.dumper_registry')
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'file:info' ]]);

        $container->register('application.file_info', function (Container $container) {
            return new FileInfo(
                $container->get('class_to_file.converter'),
                $container->get('source_code_filesystem.simple')
            );
        });

        $container->register('class_to_file_extra.rpc.handler.file_info', function (Container $container) {
            return new FileInfoHandler($container->get('application.file_info'));
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => FileInfoHandler::NAME] ]);
    }

    
    public function configure(Resolver $schema): void
    {
    }
}
