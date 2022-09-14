<?php

namespace Phpactor\Search\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<MatchToken>
 */
class MatchTokens implements Countable, IteratorAggregate
{
    /**
     * @var array<string,MatchToken>
     */
    private array $tokens;

    /**
     * @param array<string,MatchToken> $tokens
     */
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function count(): int
    {
        return count($this->tokens);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->tokens);
    }
}
