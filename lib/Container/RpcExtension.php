<?php

namespace Phpactor\Container;

use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\DependencyInjection\Container;
use Phpactor\Console\Command\RpcCommand;
use Phpactor\Rpc\HandlerRegistry;
use Phpactor\Rpc\RequestHandler\RequestHandler;
use Phpactor\Rpc\Handler\EchoHandler;
use Phpactor\Rpc\Handler\StatusHandler;
use Phpactor\Rpc\Handler\GotoDefinitionHandler;
use Phpactor\Rpc\Handler\CompleteHandler;
use Phpactor\Rpc\Handler\ClassSearchHandler;
use Phpactor\Rpc\Handler\ClassCopyHandler;
use Phpactor\Rpc\Handler\ClassMoveHandler;
use Phpactor\Rpc\Handler\ReferencesHandler;
use Phpactor\Rpc\Handler\OffsetInfoHandler;
use Phpactor\Rpc\Handler\TransformHandler;
use Phpactor\Rpc\Handler\ClassNewHandler;
use Phpactor\Rpc\Handler\ClassInflectHandler;
use Phpactor\Rpc\Handler\ContextMenuHandler;
use Phpactor\Rpc\Handler\ExtractConstantHandler;
use Phpactor\Rpc\Handler\ExtractMethodHandler;
use Phpactor\Rpc\Handler\GenerateMethodHandler;
use Phpactor\Rpc\Handler\GenerateAccessorHandler;
use Phpactor\Rpc\Handler\RenameVariableHandler;
use Phpactor\Rpc\RequestHandler\ExceptionCatchingHandler;
use Phpactor\Rpc\RequestHandler\LoggingHandler;
use Phpactor\Rpc\Handler\NavigateHandler;
use Phpactor\Rpc\Handler\OverrideMethodHandler;
use Phpactor\Rpc\Handler\CacheClearHandler;
use Phpactor\Rpc\Handler\ConfigHandler;
use Phpactor\Rpc\Handler\ImportClassHandler;

class RpcExtension implements ExtensionInterface
{
    const SERVICE_REQUEST_HANDLER = 'rpc.request_handler';

    /**
     * {@inheritDoc}
     */
    public function load(Container $container)
    {
        $container->register('rpc.command.rpc', function (Container $container) {
            return new RpcCommand(
                $container->get('rpc.request_handler'),
                $container->get('config.paths')
            );
        }, [ 'ui.console.command' => [] ]);

        $container->register(self::SERVICE_REQUEST_HANDLER, function (Container $container) {
            return new LoggingHandler(
                new ExceptionCatchingHandler(
                    new RequestHandler($container->get('rpc.handler_registry'))
                ),
                $container->get('monolog.logger')
            );
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

        $container->register('rpc.handler.class_references', function (Container $container) {
            return new ReferencesHandler(
                $container->get('reflection.reflector'),
                $container->get('application.class_references'),
                $container->get('application.method_references'),
                $container->get('source_code_filesystem.registry')
            );
        }, [ 'rpc.handler' => [] ]);

        $container->register('rpc.handler.copy_class', function (Container $container) {
            return new ClassCopyHandler(
                $container->get('application.class_copy')
            );
        }, [ 'rpc.handler' => [] ]);

        $container->register('rpc.handler.move_class', function (Container $container) {
            return new ClassMoveHandler(
                $container->get('application.class_mover'),
                $container->getParameter('rpc.class_move.filesystem')
            );
        }, [ 'rpc.handler' => [] ]);

        $container->register('rpc.handler.offset_info', function (Container $container) {
            return new OffsetInfoHandler(
                $container->get('reflection.reflector')
            );
        }, [ 'rpc.handler' => [] ]);

        $container->register('rpc.handler.transform', function (Container $container) {
            return new TransformHandler(
                $container->get('code_transform.transform')
            );
        }, [ 'rpc.handler' => [] ]);

        $container->register('rpc.handler.class_new', function (Container $container) {
            return new ClassNewHandler(
                $container->get('application.class_new')
            );
        }, [ 'rpc.handler' => [] ]);

        $container->register('rpc.handler.class_inflect', function (Container $container) {
            return new ClassInflectHandler(
                $container->get('application.class_inflect')
            );
        }, [ 'rpc.handler' => [] ]);

        $container->register('rpc.handler.context_menu', function (Container $container) {
            return new ContextMenuHandler(
                $container->get('reflection.reflector'),
                $container->get('application.helper.class_file_normalizer'),
                json_decode(file_get_contents(__DIR__ . '/config/menu.json'), true),
                $container
            );
        }, [ 'rpc.handler' => [] ]);

        $container->register('rpc.handler.extract_constant', function (Container $container) {
            return new ExtractConstantHandler(
                $container->get('code_transform.refactor.extract_constant')
            );
        }, [ 'rpc.handler' => [] ]);

        $container->register('rpc.handler.extract_method', function (Container $container) {
            return new ExtractMethodHandler(
                $container->get('code_transform.refactor.extract_method')
            );
        }, [ 'rpc.handler' => [] ]);

        $container->register('rpc.handler.generate_method', function (Container $container) {
            return new GenerateMethodHandler(
                $container->get('code_transform.refactor.generate_method')
            );
        }, [ 'rpc.handler' => [] ]);

        $container->register('rpc.handler.generate_accessor', function (Container $container) {
            return new GenerateAccessorHandler(
                $container->get('code_transform.refactor.generate_accessor')
            );
        }, [ 'rpc.handler' => [] ]);

        $container->register('rpc.handler.rename_variable', function (Container $container) {
            return new RenameVariableHandler(
                $container->get('code_transform.refactor.rename_variable')
            );
        }, [ 'rpc.handler' => [] ]);

        $container->register('rpc.handler.navigate', function (Container $container) {
            return new NavigateHandler(
                $container->get('application.navigator')
            );
        }, [ 'rpc.handler' => [] ]);

        $container->register('rpc.handler.override_method', function (Container $container) {
            return new OverrideMethodHandler(
                $container->get('reflection.reflector'),
                $container->get('code_transform.refactor.override_method')
            );
        }, [ 'rpc.handler' => [] ]);

        $container->register('rpc.handler.refactor.import_class', function (Container $container) {
            return new ImportClassHandler(
                $container->get('code_transform.refactor.class_import'),
                $container->get('application.class_search'),
                $container->getParameter('rpc.class_search.filesystem')
            );
        }, [ 'rpc.handler' => [] ]);

        $container->register('rpc.handler.cache_clear', function (Container $container) {
            return new CacheClearHandler(
                $container->get('application.cache_clear')
            );
        }, [ 'rpc.handler' => [] ]);

        $container->register('rpc.handler.status', function (Container $container) {
            return new StatusHandler(
                $container->get('application.status'),
                $container->get('config.paths')
            );
        }, [ 'rpc.handler' => [] ]);

        $container->register('rpc.handler.config', function (Container $container) {
            return new ConfigHandler($container->getParameters());
        }, [ 'rpc.handler' => [] ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultConfig()
    {
        return [
            'rpc.class_search.filesystem' => SourceCodeFilesystemExtension::FILESYSTEM_COMPOSER,
            'rpc.class_move.filesystem' => SourceCodeFilesystemExtension::FILESYSTEM_GIT,
        ];
    }
}
