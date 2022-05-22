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
use Traversable;

/**
 * @template T
 * @implements IteratorAggregate<T>
 * @implements ArrayAccess<array-key,T>
 */
abstract class AbstractReflectionCollection implements IteratorAggregate, Countable, ArrayAccess
{
    /**
     * @var array<array-key,T>
     */
    protected array $items = [];

    /**
     * @param array<array-key,T> $items
     */
    final protected function __construct(array $items)
    {
        $this->items = $items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return array-key[]
     */
    public function keys(): array
    {
        return array_keys($this->items);
    }

    /**
     * @return static
     * @param T[] $reflections
     */
    public static function fromReflections(array $reflections): self
    {
        return new static($reflections);
    }

    /**
     * @return static
     */
    public static function empty()
    {
        return new static([]);
    }

    /**
     * @return static
     * @param ReflectionCollection<T> $collection
     */
    public function merge(ReflectionCollection $collection): self
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

    /**
     * @return T
     */
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

    /**
     * @return T
     */
    public function first()
    {
        if (empty($this->items)) {
            throw new ItemNotFound(
                'Collection is empty, cannot get the first item'
            );
        }

        return reset($this->items);
    }

    /**
     * @return T
     */
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

    public function getIterator(): Traversable
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
