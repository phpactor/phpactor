<?php

namespace Phpactor\Extension\Behat;

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
use Phpactor\Extension\Symfony\SymfonyExtension;
use Phpactor\FilePathResolver\PathResolver;
use Phpactor\MapResolver\Resolver;

class BehatSuggestExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register('behat.suggest', function (Container $container) {
            $pathResolver = $container->expect(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER, PathResolver::class);

            return new PhpactorComposerSuggestor(
                $container->expect(ConfigurationExtension::SERVICE_PHPACTOR_CONFIG_LOCAL, JsonConfig::class),
                $container->get(ComposerInspector::class),
                function (JsonConfig $config, ComposerInspector $inspector) use ($pathResolver) {
                    if ($config->has(BehatExtension::PARAM_ENABLED)) {
                        return Changes::none();
                    }

                    if (!$inspector->package('behat/behat')) {
                        return Changes::none();
                    }

                    $changes = [
                        new PhpactorConfigChange('Behat BDD framework detected, enable Behat extension?', function (bool $enable) {
                            return [
                                BehatExtension::PARAM_ENABLED => $enable,
                            ];
                        })
                    ];

                    $xmlPath = 'var/cache/test/App_KernelTestDebugContainer.xml';
                    $fullXmlPath = $pathResolver->resolve('%project_root%/' . $xmlPath);

                    if (!$config->has(BehatExtension::PARAM_SYMFONY_XML_PATH)) {
                        if (file_exists($fullXmlPath)) {
                            $changes[] = new PhpactorConfigChange('Enable Behat Symfony integration?', function (bool $enable) use ($xmlPath) {
                                return [
                                    BehatExtension::PARAM_SYMFONY_XML_PATH => $xmlPath
                                ];
                            });
                        }
                    }

                    return Changes::from($changes);
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
