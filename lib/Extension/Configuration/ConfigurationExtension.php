<?php

namespace Phpactor\Extension\Configuration;

use Phpactor\Configurator\Model\ConfigManipulator;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\Core\Command\ConfigInitCommand;
use Phpactor\Extension\Core\Command\ConfigSetCommand;
use Phpactor\Extension\Configuration\Model\JsonSchemaBuilder;
use Phpactor\Extension\Core\Command\ConfigJsonSchemaCommand;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\MapResolver\Resolver;

class ConfigurationExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register(ConfigManipulator::class, function (Container $container) {
            return new ConfigManipulator(
                realpath(__DIR__ . '/../../..') . '/phpactor.schema.json',
                $container->getParameter(FilePathResolverExtension::PARAM_PROJECT_ROOT) . '/.phpactor.json'
            );
        });

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

        $container->register(ConfigSetCommand::class, function (Container $container) {
            return new ConfigSetCommand($container->get(ConfigManipulator::class));
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'config:set']]);
    }

    public function configure(Resolver $schema): void
    {
    }
}
