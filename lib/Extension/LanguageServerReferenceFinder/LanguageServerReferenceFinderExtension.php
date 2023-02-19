<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder;

use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Microsoft\PhpParser\Parser;
use Phpactor\Container\Container;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServerBridge\Converter\LocationConverter;
use Phpactor\Extension\LanguageServerReferenceFinder\Adapter\OutsourcedHighlighter;
use Phpactor\Extension\LanguageServerReferenceFinder\Command\HighlighterCommand;
use Phpactor\Extension\LanguageServerReferenceFinder\Handler\GotoDefinitionHandler;
use Phpactor\Extension\LanguageServerReferenceFinder\Handler\GotoImplementationHandler;
use Phpactor\Extension\LanguageServerReferenceFinder\Handler\HighlightHandler;
use Phpactor\Extension\LanguageServerReferenceFinder\Handler\ReferencesHandler;
use Phpactor\Extension\LanguageServerReferenceFinder\Handler\TypeDefinitionHandler;
use Phpactor\Extension\LanguageServerReferenceFinder\Adapter\TolerantHighlighter;
use Phpactor\Extension\LanguageServerReferenceFinder\Adapter\Indexer\WorkspaceUpdateReferenceFinder;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\FilePathResolver\PathResolver;
use Phpactor\Indexer\Model\Indexer;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\MapResolver\Resolver;
use Phpactor\ReferenceFinder\ClassImplementationFinder;
use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\ReferenceFinder\TypeLocator;

class LanguageServerReferenceFinderExtension implements Extension
{
    const PARAM_REFERENCE_TIMEOUT = 'language_server_reference_reference_finder.reference_timeout';


    public function load(ContainerBuilder $container): void
    {
        $this->registerHighlights($container);

        $container->register(GotoDefinitionHandler::class, function (Container $container) {
            return new GotoDefinitionHandler(
                $container->expect(LanguageServerExtension::SERVICE_SESSION_WORKSPACE, Workspace::class),
                $container->expect(ReferenceFinderExtension::SERVICE_DEFINITION_LOCATOR, DefinitionLocator::class),
                $container->get(LocationConverter::class),
                $container->get(ClientApi::class),
            );
        }, [ LanguageServerExtension::TAG_METHOD_HANDLER => [] ]);

        $container->register(TypeDefinitionHandler::class, function (Container $container) {
            return new TypeDefinitionHandler(
                $container->expect(LanguageServerExtension::SERVICE_SESSION_WORKSPACE, Workspace::class),
                $container->expect(ReferenceFinderExtension::SERVICE_TYPE_LOCATOR, TypeLocator::class),
                $container->get(LocationConverter::class),
                $container->get(ClientApi::class),
            );
        }, [ LanguageServerExtension::TAG_METHOD_HANDLER => [] ]);

        $container->register(WorkspaceUpdateReferenceFinder::class, function (Container $container) {
            return new WorkspaceUpdateReferenceFinder(
                $container->expect(LanguageServerExtension::SERVICE_SESSION_WORKSPACE, Workspace::class),
                $container->get(Indexer::class),
                $container->get(ReferenceFinder::class),
            );
        });

        $container->register(ReferencesHandler::class, function (Container $container) {
            return new ReferencesHandler(
                $container->expect(LanguageServerExtension::SERVICE_SESSION_WORKSPACE, Workspace::class),
                $container->get(WorkspaceUpdateReferenceFinder::class),
                $container->expect(ReferenceFinderExtension::SERVICE_DEFINITION_LOCATOR, DefinitionLocator::class),
                $container->get(LocationConverter::class),
                $container->get(ClientApi::class),
                $container->getParameter(self::PARAM_REFERENCE_TIMEOUT)
            );
        }, [ LanguageServerExtension::TAG_METHOD_HANDLER => [] ]);

        $container->register(GotoImplementationHandler::class, function (Container $container) {
            return new GotoImplementationHandler(
                $container->expect(LanguageServerExtension::SERVICE_SESSION_WORKSPACE, Workspace::class),
                $container->expect(ReferenceFinderExtension::SERVICE_IMPLEMENTATION_FINDER, ClassImplementationFinder::class),
                $container->get(LocationConverter::class)
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

    private function registerHighlights(ContainerBuilder $container): void
    {
        $container->register(HighlighterCommand::class, function (Container $container) {
            return new HighlighterCommand($container->get(TolerantHighlighter::class));
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => HighlighterCommand::NAME ]]);

        $container->register(TolerantHighlighter::class, function (Container $container) {
            return new TolerantHighlighter(new Parser());
        });

        $container->register(OutsourcedHighlighter::class, function (Container $container) {
            $resolver = $container->expect(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER, PathResolver::class);
            $projectPath = $resolver->resolve('%project_root%');
            return new OutsourcedHighlighter([
                __DIR__ . '/../../../bin/phpactor',
                'language-server:highlights',
            ], $projectPath);
        });

        $container->register(HighlightHandler::class, function (Container $container) {
            return new HighlightHandler(
                $container->expect(LanguageServerExtension::SERVICE_SESSION_WORKSPACE, Workspace::class),
                $container->get(TolerantHighlighter::class)
            );
        }, [ LanguageServerExtension::TAG_METHOD_HANDLER => [] ]);
    }
}
