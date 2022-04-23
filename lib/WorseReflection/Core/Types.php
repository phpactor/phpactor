<?php

namespace Phpactor\WorseReflection\Core;

use ArrayIterator;
use Closure;
use IteratorAggregate;
use Traversable;

/**
 * @template-covariant T of Type
 * @implements IteratorAggregate<T>
 */
class Types implements IteratorAggregate
{
    /**
     * @var T[]
     */
    private array $types;

    /**
     * @param T[] $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->types);
    }

    /**
     * @return T|null
     */
    public function firstOrNull(): ?Type
    {
        if (empty($this->types)) {
            return null;
        }

        return reset($this->types);
    }

    /**
     * @return Types<T>
     */
    public function filter(Closure $predicate): self
    {
        return new self(array_filter($this->types, $predicate));
    }
}
