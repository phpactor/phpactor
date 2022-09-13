<?php

namespace Phpactor\Search\Model;

use Countable;

class Matches implements Countable
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
}
