<?php

namespace Phpactor\Extension\LanguageServer\Listener;

use Generator;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\LanguageServerProtocol\TextDocumentContentChangeIncrementalEvent;
use Phpactor\LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\LanguageServer\Event\TextDocumentClosed;
use Phpactor\LanguageServer\Event\TextDocumentIncrementallyUpdated;
use Phpactor\LanguageServer\Event\TextDocumentOpened;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Psr\EventDispatcher\ListenerProviderInterface;

class IncrementalUpdateListener implements ListenerProviderInterface
{

    public function __construct(private Workspace $workspace)
    {
    }

    /**
     * @return Generator<callable>
     */
    public function getListenersForEvent(object $event): Generator
    {
        if ($event instanceof TextDocumentClosed) {
            yield function (TextDocumentClosed $closed): void {
                $this->workspace->remove($closed->identifier());
            };
            return;
        }

        if ($event instanceof TextDocumentOpened) {
            yield function (TextDocumentOpened $opened): void {
                $this->workspace->open($opened->textDocument());
            };
            return;
        }

        if ($event instanceof TextDocumentIncrementallyUpdated) {
            yield function (TextDocumentIncrementallyUpdated $updated): void {
                $this->applyEdits($updated->identifier(), $updated->events());
            };
            return;
        }
    }

    /**
     * @param TextDocumentContentChangeIncrementalEvent[] $array
     */
    public function applyEdits(VersionedTextDocumentIdentifier $versionedTextDocumentIdentifier, array $array): void
    {
        $document = $this->workspace->get($versionedTextDocumentIdentifier->uri);
        $content = $document->text;

        foreach ($array as $event) {
            $range = RangeConverter::toPhpactorRange($event->range, $content);
            $edit = TextEdit::create(
                $range->start(),
                $range->length(),
                $event->text
            );
            $content = TextEdits::fromTextEdits([$edit])->apply($content);
        }
        $this->workspace->update(
            $versionedTextDocumentIdentifier,
            $content
        );

    }
}
