<?php

namespace Phpactor\Extension\LanguageServerSymbolProvider;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServerSymbolProvider\Adapter\TolerantDocumentSymbolProvider;
use Phpactor\Extension\LanguageServerSymbolProvider\Handler\DocumentSymbolProviderHandler;
use Phpactor\Extension\LanguageServerSymbolProvider\Model\DocumentSymbolProvider;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\MapResolver\Resolver;

class LanguageServerSymbolProviderExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register(DocumentSymbolProviderHandler::class, function (Container $container) {
            return new DocumentSymbolProviderHandler(
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(DocumentSymbolProvider::class)
            );
        }, [
            LanguageServerExtension::TAG_METHOD_HANDLER => [],
        ]);
        $container->register(DocumentSymbolProvider::class, function (Container $container) {
            return new TolerantDocumentSymbolProvider(new \Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider());
        });
    }


    public function configure(Resolver $schema): void
    {
    }
}
