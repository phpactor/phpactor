<?php

namespace Phpactor\WorseReflection\Core;

use Closure;

interface Cache
{
    /**
     * @template T
     * @param Closure(): T $closure
     * @return T
     */
    public function getOrSet(string $key, Closure $closure);

    public function purge(): void;
}
