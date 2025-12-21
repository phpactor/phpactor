<?php

namespace Phpactor\WorseReflection\Core\Cache;

use Closure;
use Phpactor\WorseReflection\Core\Cache;

class TtlCache implements Cache
{
    /**
     * @var array<string, mixed>
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
    public function __construct(private float $lifetime = 5.0)
    {
    }

    public function getOrSet(string $key, Closure $setter)
    {
        $this->purgeIfNeeded();

        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $setter();
        $this->set($key, $value);

        return $this->cache[$key];
    }

    public function purge(): void
    {
        $this->cache = [];
        $this->expires = [];
    }

    public function has(string $key): bool
    {
        $this->purgeIfNeeded();
        return isset($this->cache[$key]) && $this->expires[$key] > microtime(true);
    }

    public function get(string $key): mixed
    {
        $this->purgeIfNeeded();
        return $this->has($key) ? $this->cache[$key] : null;
    }

    public function set(string $key, mixed $value): void
    {
        $this->cache[$key] = $value;
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
