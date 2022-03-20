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
}
