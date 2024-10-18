<?php

namespace Phpactor\Extension\LanguageServerIndexer\Listener;

use Phpactor\Indexer\Model\Indexer;
use Phpactor\LanguageServer\Event\TextDocumentSaved;
use Phpactor\TextDocument\Exception\TextDocumentNotFound;
use Phpactor\TextDocument\TextDocumentLocator;
use Phpactor\TextDocument\TextDocumentUri;
use Psr\EventDispatcher\ListenerProviderInterface;

class IndexOnSaveListener implements ListenerProviderInterface
{
    public function __construct(private Indexer $indexer, private TextDocumentLocator $locator)
    {
    }

    /**
     * @return iterable<callable():void>
     */
    public function getListenersForEvent($event): iterable
    {
        if ($event instanceof TextDocumentSaved) {
            yield function () use ($event): void {
                try {
                    $textDocument = $this->locator->get(
                        TextDocumentUri::fromString($event->identifier()->uri)
                    );
                } catch (TextDocumentNotFound) {
                    return;
                }
                $this->indexer->index($textDocument);

                // flush the index to make changes available to external
                // processes (i.e. the outsourced diagnostics).
                $this->indexer->flush();
            };
        }
    }
}
