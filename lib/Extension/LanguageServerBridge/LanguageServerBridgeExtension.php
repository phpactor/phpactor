<?php

namespace Phpactor\Extension\LanguageServerBridge;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServerBridge\Converter\LocationConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\WorkspaceEditConverter;
use Phpactor\TextDocument\FilesystemTextDocumentLocator;
use Phpactor\Extension\LanguageServerBridge\TextDocument\WorkspaceTextDocumentLocator;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\TextDocument\TextDocumentLocator;
use Phpactor\TextDocument\TextDocumentLocator\ChainDocumentLocator;

class LanguageServerBridgeExtension implements Extension
{
    public function configure(Resolver $schema): void
    {
    }


    public function load(ContainerBuilder $container): void
    {
        $container->register(LocationConverter::class, function (Container $container) {
            return new LocationConverter(
                $container->get(TextDocumentLocator::class)
            );
        });

        $container->register(TextEditConverter::class, function (Container $container) {
            return new TextEditConverter();
        });

        $container->register(WorkspaceEditConverter::class, function (Container $container) {
            return new WorkspaceEditConverter($container->get(TextDocumentLocator::class));
        });

        $container->register(FilesystemTextDocumentLocator::class, function (Container $container) {
            return new FilesystemTextDocumentLocator();
        });

        $container->register(WorkspaceTextDocumentLocator::class, function (Container $container) {
            return new WorkspaceTextDocumentLocator($container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE));
        });

        $container->register(TextDocumentLocator::class, function (Container $container) {
            return new ChainDocumentLocator([
                $container->get(WorkspaceTextDocumentLocator::class),
                $container->get(FilesystemTextDocumentLocator::class)
            ]);
        });
    }
}
