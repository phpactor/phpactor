<?php

namespace Phpactor\Extension\CompletionExtra;

use Phpactor\Container\Extension;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\Container\Container;
use Phpactor\Extension\CompletionExtra\Command\CompleteCommand;
use Phpactor\Extension\CompletionExtra\Application\Complete;
use Phpactor\Extension\CompletionExtra\LanguageServer\CompletionLanguageExtension;

class CompletionExtraExtension implements Extension
{
    const CLASS_COMPLETOR_LIMIT = 'completion.completor.class.limit';

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $this->registerCommands($container);
        $this->registerLanguageServer($container);
        $this->registerApplicationServices($container);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }

    private function registerCommands(ContainerBuilder $container)
    {
        $container->register('command.complete', function (Container $container) {
            return new CompleteCommand(
                $container->get('application.complete'),
                $container->get('console.dumper_registry')
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'complete' ]]);
    }

    private function registerApplicationServices(ContainerBuilder $container)
    {
        $container->register('application.complete', function (Container $container) {
            return new Complete(
                $container->get('completion.completor')
            );
        });
    }

    private function registerLanguageServer(ContainerBuilder $container)
    {
        $container->register('completion.language_server.completion', function (Container $container) {
            return new CompletionLanguageExtension(
                $container->get('language_server.session_manager'),
                $container->get('completion.completor'),
                $container->get('reflection.reflector')
            );
        }, [ 'language_server.extension' => [] ]);
    }
}
