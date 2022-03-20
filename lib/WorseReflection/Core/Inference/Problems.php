<?php

namespace Phpactor\WorseReflection\Core\Inference;

use IteratorAggregate;
use Countable;
use ArrayIterator;

final class Problems implements IteratorAggregate, Countable
{
    private $problems = [];

    private function __construct(array $problems = [])
    {
        $this->problems = $problems;
    }

    public function __toString()
    {
        $lines = [];
        /** @var SymbolContext $symbolInformation */
        foreach ($this->problems as $symbolInformation) {
            $lines[] = sprintf(
                '%s:%s %s',
                $symbolInformation->symbol()->position()->start(),
                $symbolInformation->symbol()->position()->end(),
                implode(', ', $symbolInformation->issues())
            );
        }

        return implode(PHP_EOL, $lines);
    }

    public static function create(): Problems
    {
        return new self();
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->problems);
    }

    public function add(SymbolContext $problem): void
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

    public function toArray(): array
    {
        return $this->problems;
    }

    public function merge(Problems $problems)
    {
        return new self(array_merge(
            $this->problems,
            $problems->toArray()
        ));
    }
}
