<?php

namespace Phpactor\WorseReflection\Core\Cache;

use Closure;
use Phpactor\WorseReflection\Core\Cache;

class NullCache implements Cache
{
    public function getOrSet(string $key, Closure $closure)
    {
        return $closure();
    }

    public function purge(): void
    {
    }

    public function has(string $key): bool
    {
        return false;
    }

    public function get(string $key): mixed
    {
        return null;
    }

    public function set(string $key, mixed $value): void
    {
    }

    public function remove(string $key): void
    {
    }
}
