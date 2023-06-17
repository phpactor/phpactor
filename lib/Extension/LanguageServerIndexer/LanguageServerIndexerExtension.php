<?php

namespace Phpactor\Extension\LanguageServerIndexer;

use Phpactor\AmpFsWatch\Watcher;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\LanguageServerIndexer\Handler\IndexerHandler;
use Phpactor\Extension\LanguageServerIndexer\Handler\WorkspaceSymbolHandler;
use Phpactor\Extension\LanguageServerIndexer\Listener\IndexerListener;
use Phpactor\Extension\LanguageServerIndexer\Model\WorkspaceSymbolProvider;
use Phpactor\Extension\LanguageServerIndexer\Status\IndexerStatusProvider;
use Phpactor\Extension\LanguageServerIndexer\Watcher\LanguageServerWatcher;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Indexer\Extension\IndexerExtension;
use Phpactor\Indexer\Model\Indexer;
use Phpactor\Indexer\Model\SearchClient;
use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Phpactor\LanguageServer\WorkDoneProgress\ProgressNotifier;
use Phpactor\MapResolver\Resolver;
use Phpactor\TextDocument\TextDocumentLocator;
use Psr\EventDispatcher\EventDispatcherInterface;

class LanguageServerIndexerExtension implements Extension
{
    public const WORKSPACE_SYMBOL_SEARCH_LIMIT = 'language_server_indexer.workspace_symbol_search_limit';
    public const PARAM_REINDEX_TIMEOUT = 'language_server_indexer.reindex_timeout';



    public function load(ContainerBuilder $container): void
    {
        $this->registerSessionHandler($container);

        $container->register(WorkspaceSymbolHandler::class, function (Container $container) {
            return new WorkspaceSymbolHandler(
                new WorkspaceSymbolProvider(
                    $container->get(SearchClient::class),
                    $container->get(TextDocumentLocator::class),
                    $container->getParameter(self::WORKSPACE_SYMBOL_SEARCH_LIMIT)
                )
            );
        }, [ LanguageServerExtension::TAG_METHOD_HANDLER => [] ]);

        $container->register(LanguageServerWatcher::class, function (Container $container) {
            return new LanguageServerWatcher(
                $container->has(ClientCapabilities::class) ? $container->get(ClientCapabilities::class) : null
            );
        }, [
            IndexerExtension::TAG_WATCHER => [
                'name' => 'lsp',
            ],
            LanguageServerExtension::TAG_LISTENER_PROVIDER => []
        ]);

        $container->register(IndexerStatusProvider::class, function (Container $container) {
            return new IndexerStatusProvider($container->get(Watcher::class));
        }, [
            LanguageServerExtension::TAG_STATUS_PROVIDER => [],
        ]);
    }

    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::WORKSPACE_SYMBOL_SEARCH_LIMIT => 250,
            self::PARAM_REINDEX_TIMEOUT => 300,
        ]);
        $schema->setDescriptions([
            self::PARAM_REINDEX_TIMEOUT => 'Unconditionally reindex modified files every N seconds',
        ]);
    }

    private function registerSessionHandler(ContainerBuilder $container): void
    {
        $container->register(IndexerHandler::class, function (Container $container) {
            return new IndexerHandler(
                $container->get(Indexer::class),
                $container->get(Watcher::class),
                $container->get(ClientApi::class),
                LoggingExtension::channelLogger($container, 'lspindexer'),
                $container->get(EventDispatcherInterface::class),
                $container->get(ProgressNotifier::class),
                (fn (mixed $timeout) => is_int($timeout) ? $timeout * 1000 : null)(
                    $container->parameter(self::PARAM_REINDEX_TIMEOUT)->value()
                )
            );
        }, [
            LanguageServerExtension::TAG_METHOD_HANDLER => [],
            LanguageServerExtension::TAG_SERVICE_PROVIDER => []
        ]);

        $container->register(IndexerListener::class, function (Container $container) {
            return new IndexerListener($container->get(ServiceManager::class));
        }, [
            LanguageServerExtension::TAG_LISTENER_PROVIDER => [],
        ]);
    }
}
