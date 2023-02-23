<?php

namespace Phpactor\Configurator;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<Change>
 */
class Changes implements IteratorAggregate, Countable
{
    /**
     * @param list<Change> $changes
     */
    public function __construct(private array $changes)
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
}
