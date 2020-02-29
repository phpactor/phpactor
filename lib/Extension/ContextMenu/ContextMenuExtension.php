<?php

namespace Phpactor\Extension\ContextMenu;

use Phpactor\Extension\CodeTransform\CodeTransformExtension;
use Phpactor\Extension\ContextMenu\Handler\ContextMenuHandler;
use Phpactor\Container\Extension;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Container;
use Phpactor\Extension\ContextMenu\Model\ContextMenu;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;

class ContextMenuExtension implements Extension
{
    const SERVICE_REQUEST_HANDLER = 'rpc.request_handler';

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register('rpc.handler.context_menu', function (Container $container) {
            return new ContextMenuHandler(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(CodeTransformExtension::SERVICE_CLASS_INTERESTING_OFFSET_FINDER),
                $container->get('application.helper.class_file_normalizer'),
                ContextMenu::fromArray(json_decode(file_get_contents(__DIR__ . '/menu.json'), true)),
                $container
            );
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => ContextMenuHandler::NAME] ]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
