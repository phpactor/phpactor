<?php

namespace Phpactor\WorseReflection\Core;

use Closure;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\Cache\NullCache;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class CacheForDocument
{
    const TTL_FOREVER = -1.0;

    /**
     * @var array<string,array{float,Cache}>
     */
    private array $caches = [];

    /**
     * @param Closure(): Cache $cacheFactory
     */
    public function __construct(
        private Closure $cacheFactory,
        private LoggerInterface $logger = new NullLogger(),
        private float $purgeGracePeriodSeconds = 5,
    ) {
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
        $uri = $uri->__toString();
        // if key not set, then create a new cache instance
        if (!isset($this->caches[$uri])) {
            $this->caches[$uri] = [self::TTL_FOREVER, ($this->cacheFactory)()];
        }

        // if entry has expired then reinitialize it
        $entry = $this->caches[$uri];
        if ($entry[0] > 0 && microtime(true) > $entry[0]) {
            $this->logger->debug(sprintf('[cache] purging after timeout "%s"', $uri));
            $this->caches[$uri] = [self::TTL_FOREVER, ($this->cacheFactory)()];
        }

        return $this->caches[$uri][1]->getOrSet($key, $setter);
    }

    public function purge(TextDocumentUri $uri): void
    {
        if (!isset($this->caches[$uri->__toString()])) {
            return;
        }

        if ($this->caches[$uri->__toString()][0] === self::TTL_FOREVER) {
            $this->logger->debug(sprintf('[cache] scheduling cache purge for "%s"', $uri->__toString()));
            $this->caches[$uri->__toString()][0] = microtime(true) + $this->purgeGracePeriodSeconds;
        }
    }
}
