<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use IteratorAggregate;
use Phpactor\WorseReflection\Core\Exception\ItemNotFound;
use Countable;
use Traversable;

/**
 * @template T
 * @extends IteratorAggregate<T>
 */
interface ReflectionCollection extends IteratorAggregate, Countable
{
    public function count(): int;

    /**
     * @return array<string>
     */
    public function keys(): array;

    /**
     * @param ReflectionCollection<T> $collection
     * @return ReflectionCollection<T>
     */
    public function merge(ReflectionCollection $collection);

    /**
     * @return T
     */
    public function get(string $name);

    /**
     * Return first item from the collection of throw an ItemNotFound exception.
     *
     * @return T
     * @throws ItemNotFound
     */
    public function first();

    /**
     * Return last item from the collection of throw an ItemNotFound exception.
     *
     * @throws ItemNotFound
     * @return T
     */
    public function last();

    public function has(string $name): bool;

    public function getIterator(): Traversable;
}
