<?php

namespace Phpactor\Extension\CompletionExtra;

use Phpactor\Container\Extension;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Extension\CompletionExtra\Rpc\HoverHandler;
use Phpactor\Extension\Completion\CompletionExtension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\Container\Container;
use Phpactor\Extension\CompletionExtra\Command\CompleteCommand;
use Phpactor\Extension\CompletionExtra\Application\Complete;

class CompletionExtraExtension implements Extension
{
    const CLASS_COMPLETOR_LIMIT = 'completion.completor.class.limit';

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $this->registerCommands($container);
        $this->registerApplicationServices($container);
        $this->registerRpc($container);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }

    private function registerRpc(ContainerBuilder $container)
    {
        $container->register('class_mover.handler.hover', function (Container $container) {
            return new HoverHandler(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(CompletionExtension::SERVICE_SHORT_DESC_FORMATTER)
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => HoverHandler::NAME] ]);
    }

    private function registerCommands(ContainerBuilder $container)
    {
        $container->register('command.complete', function (Container $container) {
            return new CompleteCommand(
                $container->get('application.complete'),
                $container->get('console.dumper_registry')
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'complete' ]]);
    }

    private function registerApplicationServices(ContainerBuilder $container)
    {
        $container->register('application.complete', function (Container $container) {
            return new Complete(
                $container->get(CompletionExtension::SERVICE_REGISTRY)
            );
        });
    }
}
