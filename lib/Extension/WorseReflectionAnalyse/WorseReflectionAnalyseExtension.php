<?php

namespace Phpactor\Extension\WorseReflectionAnalyse;

use Phpactor\Container\Container;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\WorseReflectionAnalyse\Command\AnalyseCommand;
use Phpactor\Extension\WorseReflectionAnalyse\Model\Analyser;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;

class WorseReflectionAnalyseExtension implements Extension
{
    public function configure(Resolver $schema): void
    {
    }

    public function load(ContainerBuilder $container): void
    {
        $this->registerCommands($container);
    }
    private function registerCommands(ContainerBuilder $container): void
    {
        $container->register(AnalyseCommand::class, function (Container $container) {
            return new AnalyseCommand(
                new Analyser(
                    $container->get(SourceCodeFilesystemExtension::SERVICE_REGISTRY),
                    $container->get(WorseReflectionExtension::SERVICE_REFLECTOR)
                )
            );
        }, [ ConsoleExtension::TAG_COMMAND => [
            'name' => 'worse:analyse',
        ]]);
    }
}
