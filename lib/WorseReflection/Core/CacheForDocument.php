<?php

namespace Phpactor\WorseReflection\Core;

use Closure;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\Cache\NullCache;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class CacheForDocument
{
    /**
     * @var array<string,Cache>
     */
    private array $caches = [];

    /**
     * @param Closure(): Cache $cacheFactory
     */
    public function __construct(private Closure $cacheFactory, private LoggerInterface $logger = new NullLogger())
    {
    }

    public static function none(): self
    {
        return new self(fn () => new NullCache());
    }

    /**
     * @template T
     * @param Closure(): T $setter
     * @return T
     */
    public function getOrSet(TextDocumentUri $uri, string $key, Closure $setter)
    {
        if (!isset($this->caches[$uri->__toString()])) {
            $this->caches[$uri->__toString()] = ($this->cacheFactory)();
        }
        $this->logger->debug(sprintf('[cache] resolved cache for "%s"', $uri->__toString()));
        return $this->caches[$uri->__toString()]->getOrSet($key, $setter);
    }

    public function purge(TextDocumentUri $uri): void
    {
        $this->logger->debug(sprintf('[cache] purging cache for "%s"', $uri->__toString()));
        unset($this->caches[$uri->__toString()]);
    }
}
