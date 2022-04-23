<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

use IteratorAggregate;
use Countable;
use ArrayIterator;
use InvalidArgumentException;
use Traversable;

/**
 * @template T
 * @implements IteratorAggregate<T>
 */
abstract class Collection implements IteratorAggregate, Countable
{
    /**
     * @var T[]
     */
    protected array $items = [];

    /**
     * @param T[] $items
     */
    protected function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @return static
     */
    public static function empty()
    {
        /** @phpstan-ignore-next-line */
        return new static([]);
    }
    
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @param T $item
     */
    public function isLast($item): bool
    {
        return end($this->items) === $item;
    }

    /**
     * Return first
     * @return T|null
     */
    public function first()
    {
        $first = reset($this->items);
        if (false === $first) {
            return null;
        }

        return $first;
    }

    
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return T
     */
    public function get(string $name)
    {
        if (!isset($this->items[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Unknown %s "%s", known items: "%s"',
                $this->singularName(),
                $name,
                implode('", "', array_keys($this->items))
            ));
        }

        return $this->items[$name];
    }

    public function has(string $name): bool
    {
        if (isset($this->items[$name])) {
            return true;
        }

        return false;
    }

    /**
     * @return static<T>
     */
    public function notIn(array $names): Collection
    {
        return new static(array_filter($this->items, function ($name) use ($names) {
            return false === in_array($name, $names);
        }, ARRAY_FILTER_USE_KEY));
    }

    /**
     * @return static<T>
     */
    public function in(array $names): Collection
    {
        return new static(array_filter($this->items, function ($name) use ($names) {
            return true === in_array($name, $names);
        }, ARRAY_FILTER_USE_KEY));
    }

    abstract protected function singularName(): string;
}
