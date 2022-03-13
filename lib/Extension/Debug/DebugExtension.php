<?php

namespace Phpactor\Extension\Debug;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\Debug\Command\DocumentExtensionsCommand;
use Phpactor\Extension\Debug\Model\ExtensionDocumentor;
use Phpactor\Extension\Core\Model\JsonSchemaBuilder;
use Phpactor\MapResolver\Resolver;

class DebugExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register(ExtensionDocumentor::class, function (Container $container) {
            return new ExtensionDocumentor(
                $container->getParameter(PhpactorContainer::PARAM_EXTENSION_CLASSES)
            );
        });
        $container->register(DocumentExtensionsCommand::class, function (Container $container) {
            return new DocumentExtensionsCommand(
                $container->get(ExtensionDocumentor::class),
            );
        }, [
            ConsoleExtension::TAG_COMMAND => [
                'name' => 'development:configuration-reference'
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
