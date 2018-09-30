<?php

namespace Phpactor\Extension\LanguageServer;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServer\Command\StartCommand;
use Phpactor\Extension\Completion\LanguageServer\CompletionHandler;
use Phpactor\Extension\WorseReflection\LanguageServer\GotoDefinitionHandler;
use Phpactor\LanguageServer\Core\Session\Manager;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Phpactor\MapResolver\Resolver;

class LanguageServerExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register('language_server.builder', function (Container $container) {

            $builder = LanguageServerBuilder::create(
                $container->get('monolog.logger'),
                $container->get('language_server.session_manager')
            );
            $builder->coreHandlers();

            foreach ($container->get('language_server.handlers') as $handler) {
                $builder->addHandler($handler);
            }

            return $builder;
        });

        $container->register('language_server.handlers', function (Container $container) {
            $handlers = [];
            foreach ($container->getServiceIdsForTag('language_server.handler') as $handlerId => $attrs) {
                $handler = $container->get($handlerId);
                $handlers[$handler->name()] = $handler;
            }

            return $handlers;
        });

        $container->register('language_server.command.lsp_start', function (Container $container) {
            return new StartCommand($container->get('language_server.builder'), array_keys($container->get('language_server.handlers')));
        }, [ 'ui.console.command' => []]);

        $container->register('language_server.session_manager', function (Container $container) {
            return new Manager();
        });
    }
}
