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
            $builder->withCoreExtension();

            foreach (array_keys($container->getServiceIdsForTag('language_server.extension')) as $extensionId) {
                $extension = $container->get($extensionId);
                $builder->addExtension($extension);
            }

            return $builder;
        });

        $container->register('language_server.command.lsp_start', function (Container $container) {
            return new StartCommand($container->get('language_server.builder'));
        }, [ 'ui.console.command' => []]);

        $container->register('language_server.session_manager', function (Container $container) {
            return new Manager();
        });
    }
}
