<?php

namespace Phpactor\Extension\PhpCodeSniffer;

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

class PhpCodeSnifferSuggestExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register('php_code_sniffer.suggest', function (Container $container) {
            return new PhpactorComposerSuggestor(
                $container->expect(ConfigurationExtension::SERVICE_PHPACTOR_CONFIG_LOCAL, JsonConfig::class),
                $container->get(ComposerInspector::class),
                function (JsonConfig $config, ComposerInspector $inspector) {
                    if ($config->has(PhpCodeSnifferExtension::PARAM_ENABLED)) {
                        return Changes::none();
                    }

                    if (!$inspector->package('squizlabs/php_codesniffer')) {
                        return Changes::none();
                    }

                    return Changes::from([
                        new PhpactorConfigChange('PHP_CodeSniffer detected, enable the PHP_CodeSniffer extension?', function (bool $enable) {
                            return [
                                PhpCodeSnifferExtension::PARAM_ENABLED => $enable,
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
