<?php

namespace Phpactor\WorseReflection\Core;

use Closure;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\Cache\NullCache;
use Phpactor\WorseReflection\Core\Cache\StaticCache;

final class CacheForDocument
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

    public static function none(): self
    {
        return new self(fn () => new NullCache());
    }

    public static function static(): self
    {
        return new self(fn () => new StaticCache());
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
