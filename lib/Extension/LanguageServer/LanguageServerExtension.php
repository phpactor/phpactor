<?php

namespace Phpactor\Extension\LanguageServer;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\LanguageServer\Command\StartCommand;
use Phpactor\Extension\LanguageServer\Extension\CoreLanguageExtension;
use Phpactor\Extension\WorseReflectionExtra\WorseReflectionExtraExtension;
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
        // disable the reflection cache for the language server
        $schema->setCallback(WorseReflectionExtraExtension::ENABLE_CACHE, function (array $config) {
            if ($config['command'] !== StartCommand::NAME) {
                return $config[WorseReflectionExtraExtension::ENABLE_CACHE];
            }

            return false;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register('language_server.builder', function (Container $container) {
            $builder = LanguageServerBuilder::create(
                $container->get(LoggingExtension::SERVICE_LOGGER),
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
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => StartCommand::NAME ]]);

        $container->register('language_server.session_manager', function (Container $container) {
            return new Manager();
        });

        $container->register('language_server.extension.core', function (Container $container) {
            return new CoreLanguageExtension($container->get('language_server.session_manager'));
        }, [ 'language_server.extension' => [] ]);
    }
}
