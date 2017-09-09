<?php

namespace Phpactor\Container;

use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\DependencyInjection\Container;
use Phpactor\Console\Command\RpcCommand;
use Phpactor\Rpc\HandlerRegistry;
use Phpactor\Rpc\RequestHandler;
use Phpactor\Rpc\Handler\EchoHandler;


class RpcExtension implements ExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(Container $container)
    {
        $container->register('rpc.command.rpc', function (Container $container) {
            return new RpcCommand($container->get('rpc.request_handler'));
        }, [ 'ui.console.command' => [] ]);

        $container->register('rpc.request_handler', function (Container $container) {
            return new RequestHandler($container->get('rpc.handler_registry'));
        });

        $container->register('rpc.handler_registry', function (Container $container) {
            $handlers = [];
            foreach (array_keys($container->getServiceIdsForTag('rpc.handler')) as $serviceId) {
                $handlers[] = $container->get($serviceId);
            }

            return new HandlerRegistry($handlers);

        });

        $container->register('rpc.handler.echo', function (Container $container) {
            return new EchoHandler();
        }, [ 'rpc.handler' => [] ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultConfig()
    {
        return [];
    }
}

