<?php

namespace Phpactor\DocblockParser\Ast;

use ArrayIterator;
use Countable;
use Generator;
use IteratorAggregate;
use Phpactor\DocblockParser\Ast\Tag\ParameterTag;

/**
 * @implements IteratorAggregate<ParameterTag|Token>
 */
class ParameterList extends Node implements IteratorAggregate, Countable
{
    protected const CHILD_NAMES = [
        'list'
    ];

    /**
     * @var array<ParameterTag|Token>
     */
    public array $list;

    /**
     * @param array<ParameterTag|Token> $list
     */
    public function __construct(array $list)
    {
        $this->list = $list;
    }

    /**
     * @return Generator<ParameterTag>
     */
    public function parameters(): Generator
    {
        foreach ($this->list as $element) {
            if ($element instanceof ParameterTag) {
                yield $element;
            }
        }
    }

    /**
     * @return ArrayIterator<int, ParameterTag|Token>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->list);
    }
    
    public function count()
    {
        return count($this->list);
    }
}
