<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\Listener;

use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\LanguageServer\Event\TextDocumentClosed;
use Phpactor\LanguageServer\Event\TextDocumentIncrementallyUpdated;
use Phpactor\LanguageServer\Event\TextDocumentOpened;
use Phpactor\LanguageServer\Event\TextDocumentUpdated;
use Phpactor\LanguageServerProtocol\TextDocumentContentChangeIncrementalEvent;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\IncrementalAstProvider;
use Psr\EventDispatcher\ListenerProviderInterface;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;

final class IncrementalAstListener implements ListenerProviderInterface
{
    public function __construct(
        private IncrementalAstProvider $provider,
        private Workspace $workspace,
    ) {
    }

    /**
     * @return iterable<mixed>
     */
    public function getListenersForEvent(object $event): iterable
    {
        if ($event instanceof TextDocumentIncrementallyUpdated) {
            yield $this->update(...);
            return;
        }

        if ($event instanceof TextDocumentClosed) {
            yield function (TextDocumentClosed $closed): void {
                $this->workspace->remove($closed->identifier());
                $this->provider->close(TextDocumentUri::fromString($closed->identifier()->uri));
            };
            return;
        }

        if ($event instanceof TextDocumentOpened) {
            yield function (TextDocumentOpened $opened): void {
                $this->workspace->open($opened->textDocument());
                $this->provider->open(TextDocumentConverter::fromLspTextItem($opened->textDocument()));
            };
            return;
        }

        if ($event instanceof TextDocumentUpdated) {
            yield function (TextDocumentUpdated $updated): void {
                $this->workspace->update($updated->identifier(), $updated->updatedText());
                $this->provider->open(TextDocumentBuilder::create($updated->updatedText())->uri($updated->identifier()->uri)->build());
            };
            return;
        }
    }

    private function update(TextDocumentIncrementallyUpdated $event): void
    {
        $uri = TextDocumentUri::fromString($event->identifier()->uri);
        $document = $this->workspace->get($event->identifier()->uri);

        // convert the text edits
        $content = $document->text;
        $edits = array_map(function (TextDocumentContentChangeIncrementalEvent $event) use (&$content) {
            $range = RangeConverter::toPhpactorRange($event->range, $content);
            $edit = TextEdit::create(
                $range->start(),
                $range->length(),
                $event->text
            );
            $content = TextEdits::one($edit)->apply($content);
            return $edit;
        }, $event->events());

        $uri = TextDocumentUri::fromString($uri);

        $ast = $this->provider->update($uri, $edits);

        $this->workspace->update(
            $event->identifier(),
            $ast->fileContents
        );
    }
}
