<?php

namespace Phpactor\Extension\LanguageServerPhpstan;

use Phpactor\ComposerInspector\ComposerInspector;
use Phpactor\Configurator\Model\JsonConfig;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Configuration\ConfigurationExtension;
use Phpactor\Extension\LanguageServerPhpstan\Configuration\PhpstanConfigSuggestor;
use Phpactor\MapResolver\Resolver;

class LanguageServerPhpstanSuggestExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register(PhpstanConfigSuggestor::class, function (Container $container) {
            return new PhpstanConfigSuggestor(
                $container->expect(ConfigurationExtension::SERVICE_PHPACTOR_CONFIG_LOCAL, JsonConfig::class),
                $container->get(ComposerInspector::class)
            );
        }, [
            ConfigurationExtension::TAG_SUGGESTOR => [],
        ]);
    }

    public function configure(Resolver $schema): void
    {
    }
}
