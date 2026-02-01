<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\Listener;

use Phpactor\LanguageServer\Event\TextDocumentClosed;
use Phpactor\LanguageServer\Event\TextDocumentIncrementallyUpdated;
use Phpactor\LanguageServer\Event\TextDocumentSaved;
use Phpactor\LanguageServer\Event\TextDocumentUpdated;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflector\TolerantSourceCodeReflector;
use Phpactor\WorseReflection\Core\CacheForDocument;
use Psr\EventDispatcher\ListenerProviderInterface;

class InvalidateDocumentCacheListener implements ListenerProviderInterface
{
    public function __construct(private CacheForDocument $cache)
    {
    }

    /**
     * @return iterable<callable>
     */
    public function getListenersForEvent($event): iterable
    {
        if ($event instanceof TextDocumentIncrementallyUpdated) {
            yield function () use ($event): void {
                $this->cache->cacheForDocument(
                    TextDocumentUri::fromString($event->identifier()->uri),
                )->remove(TolerantSourceCodeReflector::CACHE_KEY_DIAGNOSTICS);
            };
        }
        if ($event instanceof TextDocumentUpdated) {
            yield function () use ($event): void {
                $this->cache->purge(TextDocumentUri::fromString($event->identifier()->uri));
            };
        }
        if ($event instanceof TextDocumentClosed) {
            yield function () use ($event): void {
                $this->cache->purge(TextDocumentUri::fromString($event->identifier()->uri));
            };
        }
        if ($event instanceof TextDocumentSaved) {
            yield function () use ($event): void {
                $this->cache->purge(TextDocumentUri::fromString($event->identifier()->uri));
            };
        }
    }
}
