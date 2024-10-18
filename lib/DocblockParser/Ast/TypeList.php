<?php

namespace Phpactor\DocblockParser\Ast;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int,Token|Element>
 */
class TypeList extends Node implements IteratorAggregate, Countable
{
    protected const CHILD_NAMES = [
        'list'
    ];

    /**
     * @param array<Token|TypeNode> $list
     */
    public function __construct(public array $list)
    {
    }

    /**
     * @return ArrayIterator<int, Token|TypeNode>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->list);
    }

    public function count(): int
    {
        return count($this->list);
    }

    public function types(): TypeNodes
    {
        return new TypeNodes(...array_filter($this->list, function (?Element $element) {
            return $element instanceof TypeNode;
        }));
    }
}
