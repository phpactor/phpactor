<?php

namespace Phpactor\Extension\Symfony;

use Phpactor\ComposerInspector\ComposerInspector;
use Phpactor\Configurator\Adapter\Phpactor\PhpactorConfigChange;
use Phpactor\Configurator\Model\Changes;
use Phpactor\Configurator\Model\JsonConfig;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Configuration\ChangeSuggestor\PhpactorComposerSuggestor;
use Phpactor\Extension\Configuration\ConfigurationExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\FilePathResolver\PathResolver;
use Phpactor\MapResolver\Resolver;

class SymfonySuggestExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register('symfony.suggest', function (Container $container) {
            return new PhpactorComposerSuggestor(
                $container->expect(ConfigurationExtension::SERVICE_PHPACTOR_CONFIG_LOCAL, JsonConfig::class),
                $container->get(ComposerInspector::class),
                function (JsonConfig $config, ComposerInspector $inspector) use ($container) {
                    if ($config->has(SymfonyExtension::PARAM_ENABLED)) {
                        return Changes::none();
                    }

                    $xmlPath = $container->expect(
                        FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER,
                        PathResolver::class
                    )->resolve($container->getParameter(SymfonyExtension::XML_PATH));

                    dump($xmlPath);
                    if (!file_exists($xmlPath)) {
                        return Changes::none();
                    }

                    return Changes::from([
                        new PhpactorConfigChange('Symfony BDD framework detected, enable Symfony extension?', function (bool $enable) {
                            return [
                                SymfonyExtension::PARAM_ENABLED => $enable,
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
