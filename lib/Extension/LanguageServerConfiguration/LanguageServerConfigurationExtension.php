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
    public function load(ContainerBuilder $container): void
    {
        $container->register(AutoConfigListener::class, function (Container $container) {
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
    }
}
