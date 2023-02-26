<?php

namespace Phpactor\Extension\Configuration;

use Phpactor\Configurator\Adapter\Phpactor\PhpactorConfig;
use Phpactor\Configurator\Adapter\Phpactor\PhpactorConfigChangeApplicator;
use Phpactor\Configurator\Model\ChangeSuggestor;
use Phpactor\Configurator\Configurator;
use Phpactor\Configurator\Model\ChangeApplicator;
use Phpactor\Configurator\Model\ConfigManipulator;
use Phpactor\Configurator\Model\JsonConfig;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Configuration\Command\ConfigSuggestCommand;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\Core\Command\ConfigInitCommand;
use Phpactor\Extension\Core\Command\ConfigSetCommand;
use Phpactor\Extension\Configuration\Model\JsonSchemaBuilder;
use Phpactor\Extension\Core\Command\ConfigJsonSchemaCommand;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\FilePathResolver\PathResolver;
use Phpactor\MapResolver\Resolver;

class ConfigurationExtension implements Extension
{
    const TAG_SUGGESTOR = 'configurator.suggestor';
    const TAG_APPLICATOR = 'configuration.applicator';
    const SERVICE_PHPACTOR_CONFIG_LOCAL = 'configuration.config.phpactor_local';


    public function load(ContainerBuilder $container): void
    {
        $this->registerCommands($container);
        $this->registerMisc($container);

        $container->register(PhpactorConfigChangeApplicator::class, function (Container $container) {
            return new PhpactorConfigChangeApplicator($container->get(ConfigManipulator::class));
        }, [
            self::TAG_APPLICATOR => [],
        ]);

        $container->register(Configurator::class, function (Container $container) {
            $suggestors = $applicators = [];
            foreach ($container->getServiceIdsForTag(self::TAG_SUGGESTOR) as $id => $attrs) {
                $suggestors[] = $container->expect($id, ChangeSuggestor::class);
            }
            foreach ($container->getServiceIdsForTag(self::TAG_APPLICATOR) as $id => $attrs) {
                $applicators[] = $container->expect($id, ChangeApplicator::class);
            }

            return new Configurator($suggestors, $applicators);
        });

        $container->register(self::SERVICE_PHPACTOR_CONFIG_LOCAL, function (Container $container) {
            $path = $container->expect(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER, PathResolver::class)->resolve('%project_root%/.phpactor.json');
            return JsonConfig::fromPath($path);
        });
    }

    public function configure(Resolver $schema): void
    {
    }

    private function registerCommands(ContainerBuilder $container): void
    {
        $container->register(ConfigInitCommand::class, function (Container $container) {
            return new ConfigInitCommand($container->get(ConfigManipulator::class));
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'config:initialize']]);

        $container->register(ConfigJsonSchemaCommand::class, function (Container $container) {
            return new ConfigJsonSchemaCommand(
                $container->get(JsonSchemaBuilder::class)
            );
        }, [
            ConsoleExtension::TAG_COMMAND => [
                'name' => 'config:json-schema'
            ]
        ]);
        $container->register(ConfigSuggestCommand::class, function (Container $container) {
            return new ConfigSuggestCommand(
                $container->get(Configurator::class)
            );
        }, [
            ConsoleExtension::TAG_COMMAND => [
                'name' => 'config:auto'
            ]
        ]);

        $container->register(ConfigSetCommand::class, function (Container $container) {
            return new ConfigSetCommand($container->get(ConfigManipulator::class));
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'config:set']]);
    }

    private function registerMisc(ContainerBuilder $container): void
    {
        $container->register(ConfigManipulator::class, function (Container $container) {
            return new ConfigManipulator(
                realpath(__DIR__ . '/../../..') . '/phpactor.schema.json',
                $container->getParameter(FilePathResolverExtension::PARAM_PROJECT_ROOT) . '/.phpactor.json'
            );
        });
    }
}
