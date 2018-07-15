<?php

namespace Phpactor\Extension\LanguageServer;

use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServer\Command\ServeCommand;
use Phpactor\MapResolver\Resolver;

class LanguageServerExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register('language_server.command.serve', function (ContainerBuilder $container) {
            return new ServeCommand(
                $container->getParameter('language_server.address'),
                $container->getParameter('language_server.port')
            );
        }, [ 'ui.console.command' => []]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
        $schema->setDefaults([
            'language_server.address' => '127.0.0.1',
            'language_server.port' => '8383',
        ]);
    }
}
