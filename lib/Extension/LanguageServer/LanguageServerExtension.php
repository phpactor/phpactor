<?php

namespace Phpactor\Extension\LanguageServer;

use DTL\ArgumentResolver\ArgumentResolver;
use DTL\ArgumentResolver\ParamConverter\RecursiveInstantiator;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServer\Command\ServeCommand;
use Phpactor\Extension\LanguageServer\Server\MethodRegistry;
use Phpactor\Extension\LanguageServer\Server\Server;
use Phpactor\Extension\LanguageServer\Server\Dispatcher;
use Phpactor\MapResolver\Resolver;

class LanguageServerExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register('language_server.command.serve', function (ContainerBuilder $container) {
            return new ServeCommand($container->get('language_server.server'));
        }, [ 'ui.console.command' => []]);

        $container->register('language_server.server', function (ContainerBuilder $container) {
            return new Server(
                $container->get('language_server.dispatcher'),
                $container->getParameter('language_server.address'),
                $container->getParameter('language_server.port')
            );
        });

        $container->register('language_server.method_registry', function (ContainerBuilder $container) {
            return new MethodRegistry([]);
        });

        $container->register('language_server.argument_resolver', function (ContainerBuilder $container) {
            return new ArgumentResolver([
                new RecursiveInstantiator()
            ]);
        });

        $container->register('language_server.dispatcher', function (ContainerBuilder $container) {
            return new Dispatcher(
                $container->get('language_server.method_registry'), 
                $container->get('language_server.argument_resolver')
            );
        });
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
