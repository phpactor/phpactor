<?php

namespace Phpactor\DocblockParser\Ast;

use ArrayIterator;
use Iterator;
use IteratorAggregate;

/**
 * @template T of Element
 * @implements IteratorAggregate<int,T>
 */
class ElementList extends Node implements IteratorAggregate
{
    protected const CHILD_NAMES = [
        'elements',
    ];

    /**
     * @param T[] $elements
     */
    public function __construct(public array $elements)
    {
    }

    /**
     * @return ArrayIterator<int,T>
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->elements);
    }

    /**
     * @return Element[]
     */
    public function toArray(): array
    {
        return $this->elements;
    }
}
