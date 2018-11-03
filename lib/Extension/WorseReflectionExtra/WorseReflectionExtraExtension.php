<?php

namespace Phpactor\Extension\WorseReflectionExtra;

use Phpactor\Extension\WorseReflectionExtra\LanguageServer\WorseReflectionLanguageExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Container;
use Phpactor\Extension\WorseReflectionExtra\Rpc\GotoDefinitionHandler as RpcGotoDefinitionHandler;
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
    }

    public function load(ContainerBuilder $container)
    {
        $this->registerGotoDefinition($container);
        $this->registerLanguageServer($container);
        $this->registerCommands($container);
        $this->registerApplicationServices($container);
    }

    private function registerGotoDefinition(ContainerBuilder $container)
    {
        $container->register('rpc.handler.goto_definition', function (Container $container) {
            return new RpcGotoDefinitionHandler(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR)
            );
        }, [ 'rpc.handler' => [] ]);
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
        }, [ 'ui.console.command' => [ 'name' => 'offset:info' ]]);
        $container->register('command.class_reflector', function (Container $container) {
            return new ClassReflectorCommand(
                $container->get('application.class_reflector'),
                $container->get('console.dumper_registry')
            );
        }, [ 'ui.console.command' => [ 'name' => 'class:reflect' ]]);
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
}
