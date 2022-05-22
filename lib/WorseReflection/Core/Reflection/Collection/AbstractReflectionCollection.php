<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\Reflection\OldCollection\ReflectionCollection;
use Phpactor\WorseReflection\Core\Exception\ItemNotFound;
use IteratorAggregate;
use Countable;
use ArrayAccess;
use InvalidArgumentException;
use ArrayIterator;
use BadMethodCallException;
use ReturnTypeWillChange;

abstract class AbstractReflectionCollection implements IteratorAggregate, Countable, ArrayAccess
{
    protected array $items = [];

    final protected function __construct(array $items)
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

    public static function fromReflections(array $reflections)
    {
        return new static($reflections);
    }

    public static function empty(): self
    {
        return new static([]);
    }

    public function merge(ReflectionCollection $collection)
    {
        $type = $this->collectionType();

        if (false === $collection instanceof $type) {
            throw new InvalidArgumentException(sprintf(
                'Collection must be instance of "%s" got "%s"',
                $type,
                get_class($collection),
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
