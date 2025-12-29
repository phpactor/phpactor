<?php

namespace Phpactor\Extension\LanguageServer\Listener;

use Generator;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\LanguageServerProtocol\TextDocumentContentChangeIncrementalEvent;
use Phpactor\LanguageServerProtocol\VersionedTextDocumentIdentifier;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\LanguageServer\Event\TextDocumentIncrementallyUpdated;
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
        $edits = array_map(function (TextDocumentContentChangeIncrementalEvent $event) use ($document) {
            $range = RangeConverter::toPhpactorRange($event->range, $document->text);
            return TextEdit::create(
                $range->start(),
                $range->length(),
                $event->text
            );
        }, $array);

        ;
        $this->workspace->update(
            $versionedTextDocumentIdentifier,
            TextEdits::fromTextEdits($edits)->apply($document->text)
        );
    }
}
