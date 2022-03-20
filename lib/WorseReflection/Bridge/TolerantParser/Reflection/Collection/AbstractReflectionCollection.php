<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\Collection;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionCollection;
use Phpactor\WorseReflection\Core\ServiceLocator;
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
    protected ServiceLocator $serviceLocator;
    
    protected array $items = [];

    protected function __construct(ServiceLocator $serviceLocator, array $items)
    {
        $this->serviceLocator = $serviceLocator;
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

    public static function fromReflections(ServiceLocator $serviceLocator, array $reflections)
    {
        return new static($serviceLocator, $reflections);
    }

    public static function empty(ServiceLocator $serviceLocator): self
    {
        return new static($serviceLocator, []);
    }

    public function merge(ReflectionCollection $collection)
    {
        $type = $this->collectionType();

        if (false === $collection instanceof $type) {
            throw new InvalidArgumentException(sprintf(
                'Collection must be instance of "%s"',
                static::class
            ));
        }

        $items = $this->items;

        foreach ($collection as $key => $value) {
            $items[$key] = $value;
        }

        return new static($this->serviceLocator, $items);
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
