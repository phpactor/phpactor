<?php

namespace Phpactor\Extension\ContextMenu;

use Phpactor\Extension\Rpc\Command\RpcCommand;
use Phpactor\Extension\Rpc\RequestHandler\RequestHandler;
use Phpactor\Extension\Rpc\Handler\EchoHandler;
use Phpactor\Extension\Core\Rpc\StatusHandler;
use Phpactor\Extension\SourceCodeFilesystemExtra\Rpc\ClassSearchHandler;
use Phpactor\Extension\ClassMover\Rpc\ClassCopyHandler;
use Phpactor\Extension\ClassMover\Rpc\ClassMoveHandler;
use Phpactor\Extension\ClassMover\Rpc\ReferencesHandler;
use Phpactor\Extension\WorseReflection\Rpc\OffsetInfoHandler;
use Phpactor\Extension\CodeTransform\Rpc\TransformHandler;
use Phpactor\Extension\CodeTransform\Rpc\ClassNewHandler;
use Phpactor\Extension\CodeTransform\Rpc\ClassInflectHandler;
use Phpactor\Extension\ContextMenu\Handler\ContextMenuHandler;
use Phpactor\Extension\CodeTransform\Rpc\ExtractConstantHandler;
use Phpactor\Extension\CodeTransform\Rpc\ExtractMethodHandler;
use Phpactor\Extension\CodeTransform\Rpc\GenerateMethodHandler;
use Phpactor\Extension\CodeTransform\Rpc\GenerateAccessorHandler;
use Phpactor\Extension\CodeTransform\Rpc\RenameVariableHandler;
use Phpactor\Extension\Rpc\RequestHandler\ExceptionCatchingHandler;
use Phpactor\Extension\Rpc\RequestHandler\LoggingHandler;
use Phpactor\Extension\CodeTransform\Rpc\OverrideMethodHandler;
use Phpactor\Extension\Core\Rpc\CacheClearHandler;
use Phpactor\Extension\Core\Rpc\ConfigHandler;
use Phpactor\Extension\CodeTransform\Rpc\ImportClassHandler;
use Phpactor\Extension\ClassToFileConsole\Rpc\FileInfoHandler;
use Phpactor\Container\Extension;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Container;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\SourceCodeFilesystemExtra\SourceCodeFilesystemExtraExtension;

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
                $container->get('reflection.reflector'),
                $container->get('application.helper.class_file_normalizer'),
                json_decode(file_get_contents(__DIR__ . '/menu.json'), true),
                $container
            );
        }, [ 'rpc.handler' => [] ]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
