<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\Exception\ItemNotFound;
use IteratorAggregate;
use Countable;
use ArrayAccess;
use InvalidArgumentException;
use ArrayIterator;
use BadMethodCallException;
use ReturnTypeWillChange;
use Traversable;

/**
 * @template T
 * @extends IteratorAggregate<T>
 */
interface ReflectionCollection extends IteratorAggregate, Countable
{
    public function count(): int;

    /**
     * @return array-key[]
     */
    public function keys(): array;

    /**
     * @return static
     * @param ReflectionCollection<T> $collection
     */
    public function merge(ReflectionCollection $collection): self;

    /**
     * @return T
     */
    public function get(string $name);

    /**
     * @return T
     */
    public function first();

    /**
     * @return T
     */
    public function last();

    public function has(string $name): bool;
}
