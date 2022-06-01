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
}
