<?php

namespace Phpactor\DocblockParser\Ast;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use RuntimeException;
use Traversable;

/**
 * @implements IteratorAggregate<TypeNode>
 */
class TypeNodes implements IteratorAggregate, Countable
{
    /**
     * @var TypeNode[]
     */
    private readonly array $types;

    public function __construct(TypeNode ...$types)
    {
        $this->types = $types;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->types);
    }

    public function first(): TypeNode
    {
        foreach ($this->types as $type) {
            return $type;
        }

        throw new RuntimeException(sprintf(
            'List has no first element'
        ));
    }

    public function count(): int
    {
        return count($this->types);
    }
}
