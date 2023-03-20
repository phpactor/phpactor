<?php

namespace Phpactor\WorseReflection\Core\Inference;

use IteratorAggregate;
use Countable;
use ArrayIterator;

/**
 * @implements IteratorAggregate<array-key,NodeContext>
 */
final class Problems implements IteratorAggregate, Countable
{
    /**
     * @param NodeContext[] $problems
     */
    private function __construct(private array $problems = [])
    {
    }

    public function __toString()
    {
        $lines = [];
        foreach ($this->problems as $symbolInformation) {
            $lines[] = sprintf(
                '%s:%s %s',
                $symbolInformation->symbol()->position()->start()->asInt(),
                $symbolInformation->symbol()->position()->endAsInt(),
                implode(', ', $symbolInformation->issues())
            );
        }

        return implode(PHP_EOL, $lines);
    }

    public static function create(): Problems
    {
        return new self();
    }

    /**
     * @return ArrayIterator<array-key,NodeContext>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->problems);
    }

    public function add(NodeContext $problem): void
    {
        $this->problems[] = $problem;
    }

    public function none(): bool
    {
        return count($this->problems) === 0;
    }

    public function count(): int
    {
        return count($this->problems);
    }

    /**
     * @return NodeContext[]
     */
    public function toArray(): array
    {
        return $this->problems;
    }

    public function merge(Problems $problems): self
    {
        return new self(array_merge(
            $this->problems,
            $problems->toArray()
        ));
    }
}
