<?php

namespace Phpactor\Extension\Debug;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\Debug\Command\GenerateDocumentationCommand;
use Phpactor\Extension\Debug\Model\ExtensionDocumentor;
use Phpactor\Extension\Debug\Model\DocumentorRegistry;
use Phpactor\Extension\Debug\Model\DefinitionDocumentor;
use Phpactor\Extension\Configuration\Model\JsonSchemaBuilder;
use Phpactor\MapResolver\Resolver;
use RuntimeException;

class DebugExtension implements Extension
{
    public const TAG_DOCUMENTOR = 'debug.documentor';
    public const EXTENSION_DOCUMENTOR_NAME = 'extension';

    public function load(ContainerBuilder $container): void
    {
        $container->register(DocumentorRegistry::class, function (Container $container) {
            $serviceMap = [];
            foreach ($container->getServiceIdsForTag(self::TAG_DOCUMENTOR) as $serviceId => $attrs) {
                if (!isset($attrs['name'])) {
                    throw new RuntimeException(sprintf(
                        'Documentor "%s" must be provided with a "name" ' .
                        'attribute when it is registered',
                        $serviceId
                    ));
                }

                $serviceMap[$attrs['name']] = $serviceId;
            }
            return new DocumentorRegistry($container, $serviceMap);
        });

        $container->register(DefinitionDocumentor::class, function (Container $container) {
            return new DefinitionDocumentor();
        });

        $container->register(ExtensionDocumentor::class, function (Container $container) {
            return new ExtensionDocumentor(
                $container->getParameter(PhpactorContainer::PARAM_EXTENSION_CLASSES),
                $container->get(DefinitionDocumentor::class)
            );
        }, [
            self::TAG_DOCUMENTOR => ['name' => self::EXTENSION_DOCUMENTOR_NAME]
        ]);

        $container->register(GenerateDocumentationCommand::class, function (Container $container) {
            return new GenerateDocumentationCommand($container->get(DocumentorRegistry::class));
        }, [
            ConsoleExtension::TAG_COMMAND => [
                'name' => 'development:generate-documentation'
            ]
        ]);

        $container->register(JsonSchemaBuilder::class, function (Container $container) {
            return new JsonSchemaBuilder(
                'Phpactor Configration Schema',
                /** @phpstan-ignore-next-line */
                $container->getParameter(PhpactorContainer::PARAM_EXTENSION_CLASSES)
            );
        });
    }


    public function configure(Resolver $schema): void
    {
    }
}
