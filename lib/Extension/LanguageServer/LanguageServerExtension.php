<?php

namespace Phpactor\Extension\LanguageServer;

use DTL\ArgumentResolver\ArgumentResolver;
use DTL\ArgumentResolver\ParamConverter\RecursiveInstantiator;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServer\Command\ServeCommand;
use Phpactor\Extension\LanguageServer\Server\Dispatcher\ErrorCatchingDispatcher;
use Phpactor\Extension\LanguageServer\Server\Dispatcher\InitializingDispatcher;
use Phpactor\Extension\LanguageServer\Server\Dispatcher\PsrLoggingDispatcher;
use Phpactor\Extension\LanguageServer\Server\MethodRegistry;
use Phpactor\Extension\LanguageServer\Server\Method\Initialize;
use Phpactor\Extension\LanguageServer\Server\Method\TextDocument\Completion;
use Phpactor\Extension\LanguageServer\Server\Method\TextDocument\DidChange;
use Phpactor\Extension\LanguageServer\Server\Method\TextDocument\DidOpen;
use Phpactor\Extension\LanguageServer\Server\Method\TextDocument\DidSave;
use Phpactor\Extension\LanguageServer\Server\Project;
use Phpactor\Extension\LanguageServer\Server\Server;
use Phpactor\Extension\LanguageServer\Server\Dispatcher\InvokingDispatcher;
use Phpactor\Extension\LanguageServer\Server\ServerFactory;
use Phpactor\MapResolver\Resolver;

class LanguageServerExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $this->loadInfrastructure($container);
        $this->loadMethods($container);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }

    private function loadInfrastructure(ContainerBuilder $container)
    {
        $container->register('language_server.command.serve', function (Container $container) {
            return new ServeCommand($container->get('language_server.server_factory'));
        }, [ 'ui.console.command' => []]);
        
        $container->register('language_server.server_factory', function (Container $container) {
            return new ServerFactory(
                $container->get('language_server.dispatcher'),
                $container->get('monolog.logger')
            );
        });
        
        $container->register('language_server.method_registry', function (Container $container) {
            $methods = [];
            foreach ($container->getServiceIdsForTag('language_server.method') as $serviceId => $options) {
                $methods[] = $container->get($serviceId);
            }
            return new MethodRegistry($methods);
        });
        
        $container->register('language_server.argument_resolver', function (Container $container) {
            return new ArgumentResolver([
                new RecursiveInstantiator()
            ]);
        });
        
        $container->register('language_server.dispatcher', function (Container $container) {
            $dispatcher = new InvokingDispatcher(
                $container->get('language_server.method_registry'), 
                $container->get('language_server.argument_resolver')
            );
        
            $dispatcher = new InitializingDispatcher(
                $dispatcher,
                $container->get('language_server.project')
            );

            $dispatcher = new ErrorCatchingDispatcher(
                $dispatcher
            );
        
            return $dispatcher;
        });
        
        $container->register('language_server.project', function (Container $container) {
            return new Project();
        });
    }

    private function loadMethods(Container $container)
    {
        $container->register('language_server.method', function (Container $container) {
            return new Initialize($container->get('language_server.project'));
        }, [ 'language_server.method' => [] ]);

        $container->register('language_server.text_document.did_open', function (Container $container) {
            return new DidOpen($container->get('language_server.project')->workspace());
        }, [ 'language_server.method' => [] ]);

        $container->register('language_server.text_document.did_save', function (Container $container) {
            return new DidSave($container->get('language_server.project')->workspace());
        }, [ 'language_server.method' => [] ]);

        $container->register('language_server.text_document.did_change', function (Container $container) {
            return new DidChange($container->get('language_server.project')->workspace());
        }, [ 'language_server.method' => [] ]);

        $container->register('language_server.text_document.completion', function (Container $container) {
            return new Completion(
                $container->get('completion.completor'),
                $container->get('language_server.project')->workspace()
            );
        }, [ 'language_server.method' => [] ]);
    }
}
