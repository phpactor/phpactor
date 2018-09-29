<?php

namespace Phpactor\Extension\LanguageServer;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServer\Command\StartCommand;
use Phpactor\Extension\LanguageServer\Handler\CompletionHandler;
use Phpactor\Extension\LanguageServer\Handler\GotoDefinitionHandler;
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

            foreach ($container->getServiceIdsForTag('language_server.handler') as $handlerId => $attrs) {
                $builder->addHandler($container->get($handlerId));
            }

            return $builder;
        });

        $container->register('language_server.command.lsp_start', function (Container $container) {
            return new StartCommand($container->get('language_server.builder'));
        }, [ 'ui.console.command' => []]);

        $container->register('language_server.session_manager', function (Container $container) {
            return new Manager();
        });

        $container->register('language_server.handler.completion', function (Container $container) {
            return new CompletionHandler(
                $container->get('language_server.session_manager'),
                $container->get('completion.completor')
            );
        }, [ 'language_server.handler' => [] ]);

        $container->register('language_server.handler.goto_definition', function (Container $container) {
            return new GotoDefinitionHandler(
                $container->get('language_server.session_manager'),
                $container->get('reflection.reflector')
            );
        }, [ 'language_server.handler' => [] ]);
    }
}
