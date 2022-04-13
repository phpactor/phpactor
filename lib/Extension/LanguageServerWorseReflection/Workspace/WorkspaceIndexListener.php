<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\Workspace;

use Phpactor\LanguageServer\Event\TextDocumentClosed;
use Phpactor\LanguageServer\Event\TextDocumentOpened;
use Phpactor\LanguageServer\Event\TextDocumentUpdated;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentUri;
use Psr\EventDispatcher\ListenerProviderInterface;

class WorkspaceIndexListener implements ListenerProviderInterface
{
    /**
     * @var WorkspaceIndex
     */
    private $index;

    public function __construct(WorkspaceIndex $index)
    {
        $this->index = $index;
    }

    public function getListenersForEvent(object $event): iterable
    {
        if ($event instanceof TextDocumentUpdated) {
            return [[$this, 'updated']];
        }

        if ($event instanceof TextDocumentClosed) {
            return [[$this, 'closed']];
        }

        if ($event instanceof TextDocumentOpened) {
            return [[$this, 'opened']];
        }

        return [];
    }

    public function opened(TextDocumentOpened $opened): void
    {
        $item = $opened->textDocument();
        $builder = TextDocumentBuilder::create($item->text ?? '');

        if ($item->uri) {
            $builder->uri($item->uri);
        }

        if ($item->languageId) {
            $builder->language($item->languageId);
        }

        $this->index->index($builder->build());
    }

    public function updated(TextDocumentUpdated $updated): void
    {
        $this->index->update(TextDocumentUri::fromString($updated->identifier()->uri), $updated->updatedText());
    }

    public function closed(TextDocumentClosed $removed): void
    {
        $this->index->remove(TextDocumentUri::fromString($removed->identifier()->uri));
    }
}
