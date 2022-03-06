<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

use IteratorAggregate;
use Countable;
use ArrayIterator;
use InvalidArgumentException;

abstract class Collection implements IteratorAggregate, Countable
{
    protected $items = [];

    protected function __construct(array $items)
    {
        $this->items = $items;
    }

    public static function empty()
    {
        return new static([]);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    public function isLast($item): bool
    {
        return end($this->items) === $item;
    }

    /**
     * Return first
     */
    public function first()
    {
        return reset($this->items);
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->items);
    }

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

    public function notIn(array $names): Collection
    {
        return new static(array_filter($this->items, function ($name) use ($names) {
            return false === in_array($name, $names);
        }, ARRAY_FILTER_USE_KEY));
    }

    public function in(array $names): Collection
    {
        return new static(array_filter($this->items, function ($name) use ($names) {
            return true === in_array($name, $names);
        }, ARRAY_FILTER_USE_KEY));
    }

    abstract protected function singularName(): string;
}
