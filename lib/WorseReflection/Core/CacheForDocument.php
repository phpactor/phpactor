<?php

namespace Phpactor\WorseReflection\Core;

use Closure;
use Phpactor\TextDocument\TextDocumentUri;

class CacheForDocument
{
    /**
     * @var array<string,Cache>
     */
    private array $caches = [];

    /**
     * @param Closure(): Cache $cacheFactory
     */
    public function __construct(private Closure $cacheFactory)
    {
    }

    /**
     * @template T
     * @param Closure(): T $setter
     * @return T
     */
    public function getOrSet(TextDocumentUri $uri, string $key, Closure $setter)
    {
        return $this->cacheForDocument($uri)->getOrSet($key, $setter);
    }

    public function cacheForDocument(TextDocumentUri $uri): Cache
    {
        if (!isset($this->caches[$uri->__toString()])) {
            $this->caches[$uri->__toString()] = ($this->cacheFactory)();
        }

        return $this->caches[$uri->__toString()];
    }

    public function purge(TextDocumentUri $uri): void
    {
        unset($this->caches[$uri->__toString()]);
    }
}
