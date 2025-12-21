<?php

namespace Phpactor\WorseReflection\Core\Cache;

use Closure;
use Phpactor\WorseReflection\Core\Cache;
use Phpactor\WorseReflection\Core\CacheEntry;

class StaticCache implements Cache
{
    /**
     * @var array<string,CacheEntry>
     */
    private array $cache = [];

    public function getOrSet(string $key, Closure $closure)
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key]->value();
        }
        $this->cache[$key] = new CacheEntry($closure());
        return $this->cache[$key]->value();
    }

    public function purge(): void
    {
        $this->cache = [];
    }

    public function get(string $key): ?CacheEntry
    {
        return $this->cache[$key] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        $this->cache[$key] = new CacheEntry($value);
    }

    public function remove(string $key): void
    {
        unset($this->cache[$key]);
    }
}
