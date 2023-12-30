<?php

namespace Phpactor\Completion\Core;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use RuntimeException;
use Traversable;

/**
 * @implements IteratorAggregate<Suggestion>
 */
final class Suggestions implements IteratorAggregate, Countable
{
    /**
     * @var Suggestion[]
     */
    private array $suggestions;

    public function __construct(Suggestion ...$suggestions)
    {
        $this->suggestions = $suggestions;
    }

    public function at(int $index): Suggestion
    {
        if (!isset($this->suggestions[$index])) {
            throw new RuntimeException(sprintf(
                'No suggestion at index %d',
                $index
            ));
        }

        return $this->suggestions[$index];
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->suggestions);
    }

    public function count(): int
    {
        return count($this->suggestions);
    }
}
