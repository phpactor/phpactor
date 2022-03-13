<?php

namespace Phpactor\Extension\WorseReflectionExtra;

use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\Extension\WorseReflectionExtra\Rpc\OffsetInfoHandler;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Container\Extension;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Container;
use Phpactor\Extension\WorseReflectionExtra\Command\OffsetInfoCommand;
use Phpactor\Extension\WorseReflectionExtra\Application\OffsetInfo;
use Phpactor\Extension\WorseReflectionExtra\Application\ClassReflector;
use Phpactor\Extension\WorseReflectionExtra\Command\ClassReflectorCommand;
use Phpactor\MapResolver\Resolver;

class WorseReflectionExtraExtension implements Extension
{
    public function configure(Resolver $schema): void
    {
    }

    public function load(ContainerBuilder $container): void
    {
        $this->registerCommands($container);
        $this->registerApplicationServices($container);
        $this->registerRpc($container);
    }

    private function registerApplicationServices(ContainerBuilder $container): void
    {
        $container->register('application.offset_info', function (Container $container) {
            return new OffsetInfo(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get('application.helper.class_file_normalizer')
            );
        });
        $container->register('application.class_reflector', function (Container $container) {
            return new ClassReflector(
                $container->get('application.helper.class_file_normalizer'),
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR)
            );
        });
    }

    private function registerCommands(ContainerBuilder $container): void
    {
        $container->register('command.offset_info', function (Container $container) {
            return new OffsetInfoCommand(
                $container->get('application.offset_info'),
                $container->get('console.dumper_registry')
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'offset:info' ]]);
        $container->register('command.class_reflector', function (Container $container) {
            return new ClassReflectorCommand(
                $container->get('application.class_reflector'),
                $container->get('console.dumper_registry')
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'class:reflect' ]]);
    }

    private function registerRpc(ContainerBuilder $container): void
    {
        $container->register('worse_reflection_extra.rpc.handler.offset_info', function (Container $container) {
            return new OffsetInfoHandler($container->get(WorseReflectionExtension::SERVICE_REFLECTOR));
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => OffsetInfoHandler::NAME] ]);
    }
}
