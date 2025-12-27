<?php

namespace Phpactor\Configurator\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<Change>
 */
final class Changes implements IteratorAggregate, Countable
{
    /**
     * @param list<Change> $changes
     */
    public function __construct(private readonly array $changes)
    {
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->changes);
    }

    public function count(): int
    {
        return count($this->changes);
    }

    public static function none(): self
    {
        return new self([]);
    }

    /**
     * @param array<int,Change> $changes
     */
    public static function from(array $changes): self
    {
        return new self($changes);
    }
}
