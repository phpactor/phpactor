<?php

namespace Phpactor\WorseReflection\Core;

use ArrayIterator;
use Closure;
use IteratorAggregate;
use RuntimeException;
use Traversable;

/**
 * @template-covariant T of object
 * @implements IteratorAggregate<array-key,T>
 */
class NavigatorElementCollection implements IteratorAggregate
{
    /**
     * @param array<T> $elements
     */
    public function __construct(private array $elements)
    {
    }

    /**
     * @return T
     */
    public function first()
    {
        foreach ($this->elements as $element) {
            return $element;
        }
        throw new RuntimeException(
            'Collection is empty, cannot get first'
        );
    }

    /**
     * @param Closure(T): bool $predicate
     * @return T
     */
    public function firstBy(Closure $predicate)
    {
        foreach ($this->elements as $element) {
            if ($predicate($element)) {
                return $element;
            }
        }

        throw new RuntimeException(
            'No elements matched the given predicate'
        );
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->elements);
    }
}
