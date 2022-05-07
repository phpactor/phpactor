<?php

namespace Phpactor\WorseReflection\Core;

use ArrayIterator;
use IteratorAggregate;
use RuntimeException;
use Traversable;


/**
 * @template T of object
 * @implements IteratorAggregate<array-key,T>
 */
class NavigatorElementCollection implements IteratorAggregate
{
    /**
     * @var T[]
     */
    private array $elements;

    /**
     * @param array<T> $elements
     */
    public function __construct(array $elements)
    {
        $this->elements = $elements;
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

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->elements);
    }
}
