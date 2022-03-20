<?php

namespace Phpactor\WorseReflection\Core\Virtual\Collection;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionCollection;
use Phpactor\WorseReflection\Core\Exception\ItemNotFound;
use ReturnTypeWillChange;
use RuntimeException;
use IteratorAggregate;
use Countable;
use ArrayAccess;
use ArrayIterator;
use BadMethodCallException;

abstract class AbstractReflectionCollection implements IteratorAggregate, Countable, ArrayAccess
{
    protected array $items = [];

    protected function __construct(array $items)
    {
        $this->items = $items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function keys(): array
    {
        return array_keys($this->items);
    }

    public function merge(ReflectionCollection $collection): ReflectionCollection
    {
        $collectionType = $this->collectionType();

        if (false === $collection instanceof $collectionType) {
            throw new RuntimeException(sprintf(
                'Collection must be instance of "%s", got "%s"',
                $collectionType,
                get_class($collection)
            ));
        }

        $items = $this->items;

        foreach ($collection as $key => $value) {
            $items[$key] = $value;
        }

        return new static($items);
    }

    public function get(string $name)
    {
        if (!isset($this->items[$name])) {
            throw new ItemNotFound(sprintf(
                'Unknown item "%s", known items: "%s"',
                $name,
                implode('", "', array_keys($this->items))
            ));
        }

        return $this->items[$name];
    }

    public function first()
    {
        if (empty($this->items)) {
            throw new ItemNotFound(
                'Collection is empty, cannot get the first item'
            );
        }

        return reset($this->items);
    }

    public function last()
    {
        if (empty($this->items)) {
            throw new ItemNotFound(
                'Collection is empty, cannot get the last item'
            );
        }
        return end($this->items);
    }

    public function has(string $name): bool
    {
        return isset($this->items[$name]);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    #[ReturnTypeWillChange]
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    public function offsetSet($name, $value): void
    {
        throw new BadMethodCallException('Collections are immutable');
    }

    public function offsetUnset($name): void
    {
        throw new BadMethodCallException('Collections are immutable');
    }

    public function offsetExists($name): bool
    {
        return isset($this->items[$name]);
    }

    abstract protected function collectionType(): string;
}
