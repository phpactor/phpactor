<?php

namespace Phpactor\Extension\Rpc;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\Rpc\Command\RpcCommand;
use Phpactor\Extension\Rpc\Handler\EchoHandler;
use Phpactor\Extension\Rpc\Registry\LazyContainerHandlerRegistry;
use Phpactor\Extension\Rpc\RequestHandler\ExceptionCatchingHandler;
use Phpactor\Extension\Rpc\RequestHandler\LoggingHandler;
use Phpactor\Extension\Rpc\RequestHandler\RequestHandler;
use Phpactor\FilePathResolverExtension\FilePathResolverExtension;
use Phpactor\MapResolver\Resolver;
use RuntimeException;

class RpcExtension implements Extension
{
    const TAG_RPC_HANDLER = 'rpc.handler';

    public const SERVICE_REQUEST_HANDLER = 'rpc.request_handler';

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container): void
    {
        $container->register('rpc.command.rpc', function (Container $container) {
            return new RpcCommand(
                $container->get('rpc.request_handler'),
                $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER)->resolve($container->getParameter('rpc.replay_path')),
                $container->getParameter('rpc.store_replay')
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'rpc' ] ]);

        $container->register(self::SERVICE_REQUEST_HANDLER, function (Container $container) {
            return new LoggingHandler(
                new ExceptionCatchingHandler(
                    new RequestHandler($container->get('rpc.handler_registry'))
                ),
                $container->get(LoggingExtension::SERVICE_LOGGER)
            );
        });

        $container->register('rpc.handler_registry', function (Container $container) {
            $handlers = [];
            foreach ($container->getServiceIdsForTag(self::TAG_RPC_HANDLER) as $serviceId => $attrs) {
                if (!isset($attrs['name'])) {
                    throw new RuntimeException(sprintf(
                        'Handler "%s" must be provided with a "name" ' .
                        'attribute when it is registered',
                        $serviceId
                    ));
                }

                $handlers[$attrs['name']] = $serviceId;
            }

            return new LazyContainerHandlerRegistry($container, $handlers);
        });

        $this->registerHandlers($container);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            'rpc.store_replay' => false,
            'rpc.replay_path' => '%cache%/replay.json',
        ]);
    }

    private function registerHandlers(ContainerBuilder $container): void
    {
        $container->register('rpc.handler.echo', function (Container $container) {
            return new EchoHandler();
        }, [ self::TAG_RPC_HANDLER => [ 'name' => 'echo' ] ]);
    }
}
