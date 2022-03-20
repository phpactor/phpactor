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
     * @var array<string, int>
     */
    private array $expires = [];
    
    private float $lifetime;

    private $ticker = 0;

    /**
     * @var float $lifetime Lifetime in seconds
     */
    public function __construct(float $lifetime = 5.0)
    {
        $this->lifetime = $lifetime;
    }

    public function getOrSet(string $key, Closure $setter)
    {
        $now = microtime(true);

        if (isset($this->cache[$key]) && $this->expires[$key] > $now) {
            return $this->cache[$key];
        }

        $this->purgeExpired($now);
        $this->cache[$key] = $setter();
        $this->expires[$key] = microtime(true) + $this->lifetime;

        return $this->cache[$key];
    }

    public function purge(): void
    {
        $this->cache = [];
        $this->expires = [];
    }

    private function purgeExpired(string $now): void
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
