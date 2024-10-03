<?php

namespace Phpactor\WorseReflection\Core;

use ArrayIterator;
use Closure;
use IteratorAggregate;
use Phpactor\WorseReflection\Core\Type\ClassLikeType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Traversable;

/**
 * @template T of Type
 * @implements IteratorAggregate<T>
 */
final class Types implements IteratorAggregate
{
    /**
     * @param T[] $types
     */
    public function __construct(private array $types)
    {
    }

    public function __toString(): string
    {
        return implode(', ', array_map(fn (Type $t) => $t->__toString(), $this->types));
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
        if ($this->types === []) {
            return null;
        }

        return reset($this->types);
    }

    /**
     * @return Types<T>
     * @param Closure(Type): bool $predicate
     */
    public function filter(Closure $predicate): Types
    {
        return new self(array_filter($this->types, $predicate));
    }

    /**
     * @param Types<Type> $types
     * @return Types<Type>
     */
    public function merge(Types $types): self
    {
        $merged = $this->types;
        foreach ($types as $type) {
            $merged[] = $type;
        }

        return new self($merged);
    }

    /**
     * Returns all class-like types
     * @return Types<Type&ClassLikeType>
     */
    public function classLike(): Types
    {
        // @phpstan-ignore-next-line no support for conditional types https://github.com/phpstan/phpstan/issues/3853
        return $this->filter(fn (Type $type) => $type instanceof ClassLikeType);
    }

    public function at(int $index): Type
    {
        return $this->types[$index] ?? new MissingType();
    }
}
