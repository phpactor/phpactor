<?php

namespace Phpactor\Extension\CompletionRpc;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\CompletionRpc\Handler\CompleteHandler;
use Phpactor\Extension\Completion\CompletionExtension;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\MapResolver\Resolver;

class CompletionRpcExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container): void
    {
        $container->register('completion_rpc.handler', function (Container $container) {
            return new CompleteHandler($container->get(CompletionExtension::SERVICE_REGISTRY));
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => CompleteHandler::NAME] ]);
    }
}
