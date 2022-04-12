<?php

namespace Phpactor\DocblockParser\Ast;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int,Token|Element>
 */
class ArrayKeyValueList extends Node implements IteratorAggregate, Countable
{
    protected const CHILD_NAMES = [
        'list'
    ];

    /**
     * @var array<Token|ArrayKeyValueNode>
     */
    public array $list;

    /**
     * @param array<Token|ArrayKeyValueNode> $list
     */
    public function __construct(array $list)
    {
        $this->list = $list;
    }

    /**
     * @return ArrayIterator<int, Token|ArrayKeyValueNode>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->list);
    }
    
    public function count(): int
    {
        return count($this->list);
    }
    
    /**
     * @return ArrayKeyValueNode[]
     */
    public function arrayKeyValues(): array
    {
        return array_filter($this->list, function (Element $element) {
            return $element instanceof ArrayKeyValueNode;
        });
    }
}
