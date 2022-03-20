<?php

namespace Phpactor\WorseReflection\DocblockParser\Ast;

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
     * @var array<Token|TypeNode>
     */
    public array $list;

    /**
     * @param array<Token|TypeNode> $list
     */
    public function __construct(array $list)
    {
        $this->list = $list;
    }

    /**
     * @return ArrayIterator<int, Token|TypeNode>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->list);
    }
    
    public function count()
    {
        return count($this->list);
    }
    
    public function types(): TypeNodes
    {
        return new TypeNodes(...array_filter($this->list, function (Element $element) {
            return $element instanceof TypeNode;
        }));
    }
}
