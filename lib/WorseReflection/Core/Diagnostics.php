<?php

namespace Phpactor\WorseReflection\Core;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @template T of Diagnostic
 * @implements IteratorAggregate<T>
 */
final class Diagnostics implements IteratorAggregate, Countable
{
    /**
     * @var T[]
     */
    private array $diagnostics;

    /**
     * @param T[] $diagnostics
     */
    public function __construct(array $diagnostics)
    {
        $this->diagnostics = $diagnostics;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->diagnostics);
    }

    public function count(): int
    {
        return count($this->diagnostics);
    }

    /**
     * @template C of Diagnostic
     * @param class-string<C> $classFqn
     * @return self<C>
     */
    public function byClass(string $classFqn): self
    {
         // @phpstan-ignore-next-line
        return new self(array_filter($this->diagnostics, fn (Diagnostic $d) => $d instanceof $classFqn));
    }
}
