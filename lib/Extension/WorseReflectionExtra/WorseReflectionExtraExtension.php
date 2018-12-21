<?php

namespace Phpactor\Extension\WorseReflectionExtra;

use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\LanguageServer\Command\StartCommand;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\Extension\WorseReflectionExtra\LanguageServer\WorseReflectionLanguageExtension;
use Phpactor\Extension\WorseReflectionExtra\Rpc\OffsetInfoHandler;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Container;
use Phpactor\Extension\WorseReflectionExtra\Command\OffsetInfoCommand;
use Phpactor\Extension\WorseReflectionExtra\Application\OffsetInfo;
use Phpactor\Extension\WorseReflectionExtra\Application\ClassReflector;
use Phpactor\Extension\WorseReflectionExtra\Command\ClassReflectorCommand;

class WorseReflectionExtraExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
        // disable the reflection cache for the language server
        $schema->setCallback(WorseReflectionExtension::PARAM_ENABLE_CACHE, function (array $config) {
            if (class_exists(StartCommand::class) && $config['command'] === StartCommand::NAME) {
                return false;
            }

            return $config[WorseReflectionExtension::PARAM_ENABLE_CACHE];
        });
    }

    public function load(ContainerBuilder $container)
    {
        $this->registerLanguageServer($container);
        $this->registerCommands($container);
        $this->registerApplicationServices($container);
        $this->registerRpc($container);
    }

    private function registerApplicationServices(ContainerBuilder $container)
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

    private function registerCommands(ContainerBuilder $container)
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

    private function registerLanguageServer(ContainerBuilder $container)
    {
        $container->register('reflection.language_server.extension', function (Container $container) {
            return new WorseReflectionLanguageExtension(
                $container->get('language_server.session_manager'),
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR)
            );
        }, [ 'language_server.extension' => [] ]);
    }

    private function registerRpc(ContainerBuilder $container)
    {
        $container->register('worse_reflection_extra.rpc.handler.offset_info', function (Container $container) {
            return new OffsetInfoHandler($container->get(WorseReflectionExtension::SERVICE_REFLECTOR));
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => OffsetInfoHandler::NAME] ]);
    }
}
