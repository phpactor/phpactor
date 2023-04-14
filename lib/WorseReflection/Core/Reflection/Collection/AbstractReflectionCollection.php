<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Phpactor\WorseReflection\Core\Exception\ItemNotFound;
use ArrayIterator;
use Traversable;

/**
 * @template T
 * @implements ReflectionCollection<T>
 */
abstract class AbstractReflectionCollection implements ReflectionCollection
{
    /**
     * @param array<array-key,T> $items
     */
    final protected function __construct(protected array $items)
    {
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
    public static function empty(): self
    {
        return new static([]);
    }

    /**
     * @return static
     * @param AbstractReflectionCollection<T> $collection
     */
    public function merge(ReflectionCollection $collection): self
    {
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
     * @return T|null
     */
    public function firstOrNull()
    {
        return reset($this->items) ?: null;
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

    /**
     * @return static
     */
    public function byMemberClass(string $fqn): ReflectionCollection
    {
        return new static(array_filter($this->items, function (object $member) use ($fqn) {
            return $member instanceof $fqn;
        }));
    }
}
