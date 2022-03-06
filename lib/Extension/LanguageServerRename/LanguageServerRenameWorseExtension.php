<?php

namespace Phpactor\Extension\LanguageServerRename;

use Phpactor\ClassMover\ClassMover;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\LanguageServerReferenceFinder\Adapter\Indexer\WorkspaceUpdateReferenceFinder;
use Phpactor\Extension\LanguageServerRename\Adapter\ClassMover\FileRenamer as PhpactorFileRenamer;
use Phpactor\Extension\LanguageServerRename\Adapter\ClassToFile\ClassToFileUriToNameConverter;
use Phpactor\Extension\LanguageServerRename\Adapter\ReferenceFinder\ClassMover\ClassRenamer;
use Phpactor\Extension\LanguageServerRename\Adapter\ReferenceFinder\MemberRenamer;
use Phpactor\Extension\LanguageServerRename\Adapter\ReferenceFinder\VariableRenamer;
use Phpactor\Extension\LanguageServerRename\Model\FileRenamer;
use Phpactor\Extension\LanguageServerRename\Model\FileRenamer\LoggingFileRenamer;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\Indexer\Model\Indexer;
use Phpactor\Indexer\Model\QueryClient;
use Phpactor\MapResolver\Resolver;
use Phpactor\ReferenceFinder\DefinitionAndReferenceFinder;
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\TextDocument\TextDocumentLocator;
use Phpactor\WorseReferenceFinder\TolerantVariableReferenceFinder;

class LanguageServerRenameWorseExtension implements Extension
{
    public const TAG_RENAMER = 'language_server_rename.renamer';
    public const PARAM_FILE_RENAME_LISTENER = 'language_server_rename.file_rename_listener';

    /**

     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container): void
    {
        $container->register(VariableRenamer::class, function (Container $container) {
            return new VariableRenamer(
                new TolerantVariableReferenceFinder(
                    $container->get('worse_reflection.tolerant_parser'),
                    true
                ),
                $container->get(TextDocumentLocator::class),
                $container->get('worse_reflection.tolerant_parser')
            );
        }, [
            LanguageServerRenameExtension::TAG_RENAMER => []
        ]);

        $container->register(MemberRenamer::class, function (Container $container) {
            return new MemberRenamer(
                $container->get(DefinitionAndReferenceFinder::class),
                $container->get(TextDocumentLocator::class),
                $container->get('worse_reflection.tolerant_parser')
            );
        }, [
            LanguageServerRenameExtension::TAG_RENAMER => []
        ]);

        $container->register(ClassRenamer::class, function (Container $container) {
            return new ClassRenamer(
                $container->get(DefinitionAndReferenceFinder::class),
                $container->get(TextDocumentLocator::class),
                $container->get('worse_reflection.tolerant_parser'),
                $container->get(ClassMover::class)
            );
        }, [
            LanguageServerRenameExtension::TAG_RENAMER => []
        ]);

        $container->register(DefinitionAndReferenceFinder::class, function (Container $container) {
            // wrap the definiton and reference finder to update the index with the current workspace
            return new WorkspaceUpdateReferenceFinder(
                $container->get(LanguageServerExtension::SERVICE_SESSION_WORKSPACE),
                $container->get(Indexer::class),
                new DefinitionAndReferenceFinder(
                    $container->get(ReferenceFinderExtension::SERVICE_DEFINITION_LOCATOR),
                    $container->get(ReferenceFinder::class)
                )
            );
        });

        $container->register(FileRenamer::class, function (Container $container) {
            $renamer = new PhpactorFileRenamer(
                new ClassToFileUriToNameConverter($container->get(ClassToFileExtension::SERVICE_CONVERTER)),
                $container->get(TextDocumentLocator::class),
                $container->get(QueryClient::class),
                $container->get(ClassMover::class)
            );
            return new LoggingFileRenamer(
                $renamer,
                $container->get(LoggingExtension::SERVICE_LOGGER)
            );
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema): void
    {
    }
}
