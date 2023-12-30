<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer;

use Phpactor\ComposerInspector\ComposerInspector;
use Phpactor\Configurator\Adapter\Phpactor\PhpactorConfigChange;
use Phpactor\Configurator\Model\Changes;
use Phpactor\Configurator\Model\JsonConfig;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Configuration\ChangeSuggestor\PhpactorComposerSuggestor;
use Phpactor\Extension\Configuration\ConfigurationExtension;
use Phpactor\MapResolver\Resolver;

class LanguageServerPhpCsFixerSuggestExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register('language_server_php_cs_fixer.suggest', function (Container $container) {
            return new PhpactorComposerSuggestor(
                $container->expect(ConfigurationExtension::SERVICE_PHPACTOR_CONFIG_LOCAL, JsonConfig::class),
                $container->get(ComposerInspector::class),
                function (JsonConfig $config, ComposerInspector $inspector) {
                    if ($config->has(LanguageServerPhpCsFixerExtension::PARAM_ENABLED)) {
                        return Changes::none();
                    }

                    if (!$inspector->package('friendsofphp/php-cs-fixer')) {
                        return Changes::none();
                    }

                    return Changes::from([
                        new PhpactorConfigChange('PHP-CS-Fixer detected, enable the PHP-CS-Fixer extension?', function (bool $enable) {
                            return [
                                LanguageServerPhpCsFixerExtension::PARAM_ENABLED => $enable,
                            ];
                        })
                    ]);
                }
            );
        }, [
            ConfigurationExtension::TAG_SUGGESTOR => [],
        ]);
    }

    public function configure(Resolver $schema): void
    {
    }
}
