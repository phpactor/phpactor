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

    private ?float $lifetimeStart = null;

    /**
     * @var float $lifetime Lifetime in seconds
     */
    public function __construct(private float $lifetime = 5.0)
    {
    }

    public function getOrSet(string $key, Closure $setter)
    {
        $now = microtime(true);
        if (null === $this->lifetimeStart) {
            $this->lifetimeStart = $now;
        }

        if (isset($this->cache[$key]) && $this->expires[$key] > $now) {
            return $this->cache[$key];
        }

        $elapsed = $now - $this->lifetimeStart;

        if ($elapsed >= $this->lifetime) {
            $this->purgeExpired($now);
            $this->lifetimeStart = $now;
        }

        $this->cache[$key] = $setter();
        $this->expires[$key] = microtime(true) + $this->lifetime;

        return $this->cache[$key];
    }

    public function purge(): void
    {
        $this->cache = [];
        $this->expires = [];
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
