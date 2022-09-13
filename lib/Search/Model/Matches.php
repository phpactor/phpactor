<?php

namespace Phpactor\Search\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<PatternMatch>
 */
class Matches implements Countable, IteratorAggregate
{
    /**
     * @var PatternMatch[]
     */
    private array $matches;

    /**
     * @param PatternMatch[] $matches
     */
    public function __construct(array $matches)
    {
        $this->matches = $matches;
    }

    public static function none(): self
    {
        return new self([]);
    }

    /**
     * @return PatternMatch[]
     */
    public function matches(): array
    {
        return $this->matches;
    }

    public function count(): int
    {
        return count($this->matches);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->matches);
    }
}
