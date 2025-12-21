<?php

namespace Phpactor\WorseReflection\Core\Cache;

use Closure;
use Phpactor\WorseReflection\Core\Cache;

class StaticCache implements Cache
{
    /**
     * @var array<string,mixed>
     */
    private array $cache = [];

    /**
     * @return mixed
     */
    public function getOrSet(string $key, Closure $closure)
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        $this->cache[$key] = $closure();
        return $this->cache[$key];
    }

    public function purge(): void
    {
        $this->cache = [];
    }

    public function has(string $key): bool
    {
        return isset($this->cache[$key]);
    }

    public function get(string $key): mixed
    {
        return $this->cache[$key] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        $this->cache[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($this->cache[$key]);
    }
}
