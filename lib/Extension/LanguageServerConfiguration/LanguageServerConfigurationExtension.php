<?php

namespace Phpactor\Extension\LanguageServerConfiguration;

use Phpactor\Configurator\Configurator;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServerConfiguration\Listener\AutoConfigListener;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\MapResolver\Resolver;

class LanguageServerConfigurationExtension implements Extension
{
    public const AUTO_CONFIG = 'language_server_configuration.auto_config';

    public function load(ContainerBuilder $container): void
    {
        $container->register(AutoConfigListener::class, function (Container $container) {
            if (false === $container->parameter(self::AUTO_CONFIG)->bool()) {
                return null;
            }

            return new AutoConfigListener(
                $container->get(Configurator::class),
                $container->get(ClientApi::class),
            );
        }, [
            LanguageServerExtension::TAG_LISTENER_PROVIDER => [],
        ]);
    }

    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::AUTO_CONFIG => true,
        ]);
        $schema->setDescriptions([
            self::AUTO_CONFIG => 'Prompt to enable extensions which apply to your project on language server start'
        ]);
        $schema->setTypes([
            self::AUTO_CONFIG => 'boolean'
        ]);

    }
}
