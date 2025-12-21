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

    public function has(string $key): bool;

    public function get(string $key): mixed;

    public function set(string $key, mixed $value): void;

    public function remove(string $key): void;

    public function purge(): void;
}
