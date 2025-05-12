<?php

namespace Phpactor\WorseReflection\Core\Cache;

use Closure;
use Phpactor\Stats;
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
            Stats::inc(sprintf('%s.hit', substr($key, 0, (int)strpos($key, '.'))));
            return $this->cache[$key];
        }
        $this->cache[$key] = $closure();
        Stats::inc(sprintf('%s.miss', substr($key, 0, (int)strpos($key, '.'))));
        return $this->cache[$key];
    }

    public function purge(): void
    {
        $this->cache = [];
    }
}
