<?php

namespace Phpactor\WorseReflection\Core\Cache;

use Closure;
use Phpactor\WorseReflection\Core\Cache;
use Phpactor\WorseReflection\Core\CacheEntry;

class TtlCache implements Cache
{
    /**
     * @var array<string, CacheEntry>
     */
    private array $cache = [];

    /**
     * @var array<string, float>
     */
    private array $expires = [];

    private ?float $epoch = null;

    /**
     * @var float $lifetime Lifetime in seconds
     */
    public function __construct(private readonly float $lifetime = 5.0)
    {
    }

    public function getOrSet(string $key, Closure $setter)
    {
        $this->purgeIfNeeded();

        $entry = $this->get($key);

        if (null !== $entry) {
            return $entry->value();
        }

        $value = $setter();
        $this->set($key, $value);

        return $this->cache[$key]->value();
    }

    public function purge(): void
    {
        $this->cache = [];
        $this->expires = [];
    }

    public function get(string $key): ?CacheEntry
    {
        $this->purgeIfNeeded();
        return $this->cache[$key] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        $this->cache[$key] = new CacheEntry($value);
        $this->expires[$key] = microtime(true) + $this->lifetime;
    }

    public function remove(string $key): void
    {
        unset($this->cache[$key]);
    }

    private function purgeIfNeeded(?int $now = null): void
    {
        $now = $now ?? microtime(true);

        if (null === $this->epoch) {
            $this->epoch = $now;
            return;
        }

        $elapsed = $now - $this->epoch;

        if ($elapsed >= $this->lifetime) {
            $this->purgeExpired($now);
            $this->epoch = $now;
        }
    }

    private function purgeExpired(float $now): void
    {
        foreach ($this->expires as $key => $expires) {
            if ($expires > $now) {
                continue;
            }

            unset($this->expires[$key]);
            unset($this->cache[$key]);
        }
    }
}
