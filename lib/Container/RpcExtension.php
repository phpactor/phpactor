<?php

namespace Phpactor\Container;

use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\DependencyInjection\Container;
use Phpactor\Console\Command\RpcCommand;
use Phpactor\Rpc\HandlerRegistry;
use Phpactor\Rpc\RequestHandler;
use Phpactor\Rpc\Handler\EchoHandler;
use Phpactor\Rpc\Handler\GotoDefinitionHandler;
use Phpactor\Rpc\Handler\CompleteHandler;
use Phpactor\Rpc\Handler\ClassSearchHandler;

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

        $this->registerHandlers($container);
    }

    private function registerHandlers(Container $container)
    {
        $container->register('rpc.handler.echo', function (Container $container) {
            return new EchoHandler();
        }, [ 'rpc.handler' => [] ]);

        $container->register('rpc.handler.goto_definition', function (Container $container) {
            return new GotoDefinitionHandler(
                $container->get('reflection.reflector')
            );
        }, [ 'rpc.handler' => [] ]);

        $container->register('rpc.handler.complete', function (Container $container) {
            return new CompleteHandler(
                $container->get('application.complete')
            );
        }, [ 'rpc.handler' => [] ]);

        $container->register('rpc.handler.class_search', function (Container $container) {
            return new ClassSearchHandler(
                $container->get('application.class_search')
            );
        }, [ 'rpc.handler' => [] ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultConfig()
    {
        return [
            'rpc.class_search.filesystem' => SourceCodeFilesystemExtension::FILESYSTEM_COMPOSER
        ];
    }
}
