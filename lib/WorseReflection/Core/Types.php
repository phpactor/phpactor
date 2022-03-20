<?php

namespace Phpactor\WorseReflection\Core;

use IteratorAggregate;
use Countable;
use ArrayIterator;

/**
 * @implements IteratorAggregate<Type>
 */
final class Types implements IteratorAggregate, Countable
{
    private $types = [];

    private function __construct($inferredTypes)
    {
        foreach ($inferredTypes as $item) {
            $this->add($item);
        }
    }

    public function __toString(): string
    {
        return implode('|', array_map(fn (Type $type) => $type->__toString(), $this->types));
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function best(): Type
    {
        foreach ($this->types as $type) {
            return $type;
        }

        return TypeFactory::unknown();
    }

    /**
     * @param Type[] $inferredTypes
     */
    public static function fromTypes(array $inferredTypes): Types
    {
        return new self($inferredTypes);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->types);
    }

    public function merge(Types $types) : Types
    {
        return new self(array_merge(
            $this->types,
            $types->types
        ));
    }
    
    public function count(): int
    {
        return count($this->types);
    }

    private function add(Type $item): void
    {
        $this->types[] = $item;
    }
}
