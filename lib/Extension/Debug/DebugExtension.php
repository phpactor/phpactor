<?php

namespace Phpactor\Extension\Debug;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\Debug\Command\GenerateDocumentationCommand;
use Phpactor\Extension\Debug\Model\ExtensionDocumentor;
use Phpactor\Extension\Debug\Model\RpcCommandDocumentor;
use Phpactor\Extension\Debug\Model\DefinitionDocumentor;
use Phpactor\Extension\Core\Model\JsonSchemaBuilder;
use Phpactor\MapResolver\Resolver;

class DebugExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register(DefinitionDocumentor::class, function (Container $container) {
            return new DefinitionDocumentor();
        });

        $container->register(ExtensionDocumentor::class, function (Container $container) {
            return new ExtensionDocumentor(
                $container->getParameter(PhpactorContainer::PARAM_EXTENSION_CLASSES),
                $container->get(DefinitionDocumentor::class)
            );
        });

        $container->register(RpcCommandDocumentor::class, function (Container $container) {
            return new RpcCommandDocumentor(
                $container->get('rpc.handler_registry'),
                $container->get(DefinitionDocumentor::class)
            );
        });

        $container->register('generate_documentation_command.extensions', function (Container $container) {
            return new GenerateDocumentationCommand($container->get(ExtensionDocumentor::class));
        }, [
            ConsoleExtension::TAG_COMMAND => [
                'name' => 'development:configuration-reference'
            ]
        ]);

        $container->register('generate_documentation_command.command', function (Container $container) {
            return new GenerateDocumentationCommand($container->get(RpcCommandDocumentor::class));
        }, [
            ConsoleExtension::TAG_COMMAND => [
                'name' => 'development:command-reference'
            ]
        ]);

        $container->register(JsonSchemaBuilder::class, function (Container $container) {
            return new JsonSchemaBuilder(
                'Phpactor Configration Schema',
                $container->getParameter(PhpactorContainer::PARAM_EXTENSION_CLASSES)
            );
        });
    }


    public function configure(Resolver $schema): void
    {
    }
}
