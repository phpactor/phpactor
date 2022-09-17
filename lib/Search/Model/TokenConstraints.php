<?php

namespace Phpactor\Search\Model;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<TokenConstraint>
 */
class TokenConstraints implements IteratorAggregate
{
    /**
     * @var TokenConstraint[]
     */
    private array $filters;

    public function __construct(TokenConstraint ...$filters)
    {
        $this->filters = $filters;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->filters);
    }
}
