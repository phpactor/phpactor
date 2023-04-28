<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder;

use Microsoft\PhpParser\Parser;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServerBridge\Converter\LocationConverter;
use Phpactor\Extension\LanguageServerCompletion\LanguageServerCompletionExtension;
use Phpactor\Extension\LanguageServerReferenceFinder\Handler\GotoDefinitionHandler;
use Phpactor\Extension\LanguageServerReferenceFinder\Handler\GotoImplementationHandler;
use Phpactor\Extension\LanguageServerReferenceFinder\Handler\HighlightHandler;
use Phpactor\Extension\LanguageServerReferenceFinder\Handler\ReferencesHandler;
use Phpactor\Extension\LanguageServerReferenceFinder\Handler\TypeDefinitionHandler;
use Phpactor\Extension\LanguageServerReferenceFinder\Model\Highlighter;
use Phpactor\Extension\LanguageServerReferenceFinder\Adapter\Indexer\WorkspaceUpdateReferenceFinder;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\Indexer\Model\Indexer;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\MapResolver\Resolver;
use Phpactor\ReferenceFinder\ReferenceFinder;

class LanguageServerReferenceFinderExtension implements Extension
{
    const PARAM_REFERENCE_TIMEOUT = 'language_server_reference_reference_finder.reference_timeout';


    public function load(ContainerBuilder $container): void
    {
        $container->register(GotoDefinitionHandler::class, function (Container $container) {
            $documentModifiers = [];

            foreach (array_keys($container->getServiceIdsForTag(LanguageServerCompletionExtension::TAG_DOCUMENT_MODIFIER)) as $serviceId) {
                $documentModifier = $container->get($serviceId);
                if (null === $documentModifier) {
                    continue;
                }
                $documentModifiers[] = $documentModifier;
            }

            return new GotoDefinitionHandler(
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(ReferenceFinderExtension::SERVICE_DEFINITION_LOCATOR),
                $container->get(LocationConverter::class),
                $container->get(ClientApi::class),
                $documentModifiers
            );
        }, [ LanguageServerExtension::TAG_METHOD_HANDLER => [] ]);

        $container->register(TypeDefinitionHandler::class, function (Container $container) {
            return new TypeDefinitionHandler(
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(ReferenceFinderExtension::SERVICE_TYPE_LOCATOR),
                $container->get(LocationConverter::class),
                $container->get(ClientApi::class),
            );
        }, [ LanguageServerExtension::TAG_METHOD_HANDLER => [] ]);

        $container->register(WorkspaceUpdateReferenceFinder::class, function (Container $container) {
            return new WorkspaceUpdateReferenceFinder(
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(Indexer::class),
                $container->get(ReferenceFinder::class),
            );
        });

        $container->register(ReferencesHandler::class, function (Container $container) {
            return new ReferencesHandler(
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(WorkspaceUpdateReferenceFinder::class),
                $container->get(ReferenceFinderExtension::SERVICE_DEFINITION_LOCATOR),
                $container->get(LocationConverter::class),
                $container->get(ClientApi::class),
                $container->getParameter(self::PARAM_REFERENCE_TIMEOUT)
            );
        }, [ LanguageServerExtension::TAG_METHOD_HANDLER => [] ]);

        $container->register(GotoImplementationHandler::class, function (Container $container) {
            return new GotoImplementationHandler(
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(ReferenceFinderExtension::SERVICE_IMPLEMENTATION_FINDER),
                $container->get(LocationConverter::class)
            );
        }, [ LanguageServerExtension::TAG_METHOD_HANDLER => [] ]);

        $container->register(HighlightHandler::class, function (Container $container) {
            return new HighlightHandler(
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                new Highlighter(new Parser())
            );
        }, [ LanguageServerExtension::TAG_METHOD_HANDLER => [] ]);
    }


    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_REFERENCE_TIMEOUT => 60
        ]);
        $schema->setDescriptions([
            self::PARAM_REFERENCE_TIMEOUT => 'Stop searching for references after this time (in seconds) has expired',
        ]);
    }
}
