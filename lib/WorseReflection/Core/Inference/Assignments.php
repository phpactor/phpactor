<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Countable;
use IteratorAggregate;
use RuntimeException;
use ArrayIterator;

/**
 * @implements IteratorAggregate<array-key,Variable>
 */
abstract class Assignments implements Countable, IteratorAggregate
{
    /**
     * @var array<array-key,array{int,Variable}>
     */
    private array $variables = [];

    /**
     * @param array<array-key, array{int, Variable}> $variables
     */
    final public function __construct(array $variables)
    {
        foreach ($variables as [$offset, $variable]) {
            $this->variables[] = [ $offset, $variable ];
        }
    }

    public function add(int $offset, Variable $variable): void
    {
        $this->variables[] = [
            $offset, $variable
        ];
    }

    /**
     * @return self
     */
    public function byName(string $name): Assignments
    {
        return new static(array_filter($this->variables, function (array $pair) use ($name) {
            return $pair[1]->isNamed($name);
        }));
    }

    public function lessThanOrEqualTo(int $offset): Assignments
    {
        return new static(array_filter($this->variables, function (array $pair) use ($offset) {
            return $pair[0] <= $offset;
        }));
    }

    public function lessThan(int $offset): Assignments
    {
        return new static(array_filter($this->variables, function (array $pair) use ($offset) {
            return $pair[0] < $offset;
        }));
    }

    public function greaterThan(int $offset): Assignments
    {
        return new static(array_filter($this->variables, function (array $pair) use ($offset) {
            return $pair[0] > $offset;
        }));
    }

    public function greaterThanOrEqualTo(int $offset): Assignments
    {
        return new static(array_filter($this->variables, function (array $pair) use ($offset) {
            return $pair[0] >= $offset;
        }));
    }

    public function first(): Variable
    {
        $first = reset($this->variables);

        if (!$first) {
            throw new RuntimeException(
                'Variable collection is empty'
            );
        }

        return $first[1];
    }

    public function atIndex(int $index): Variable
    {
        $variables = array_values($this->variables);
        if (!isset($variables[$index])) {
            throw new RuntimeException(sprintf(
                'No variable at index "%s"',
                $index
            ));
        }

        return $variables[$index][1];
    }

    public function last(): Variable
    {
        $last = end($this->variables);

        if (!$last) {
            throw new RuntimeException(
                'Cannot get last, variable collection is empty'
            );
        }

        return $last[1];
    }
    
    public function count(): int
    {
        return count($this->variables);
    }

    /**
     * @return ArrayIterator<array-key,Variable>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator(array_map(fn (array $pair) => $pair[1], $this->variables));
    }

    public function merge(Assignments $variables): Assignments
    {
        foreach ($variables->variables as $pair) {
            $this->variables[] = $pair;
        }

        return $this;
    }

    public function replace(Variable $existing, Variable $replacement): void
    {
        foreach ($this->variables as $index => [$offset, $variable]) {
            if ($variable !== $existing) {
                continue;
            }
            $this->variables[$index] = [$offset, $replacement];
        }
    }

    public function equalTo(int $offset): Assignments
    {
        return new static(array_filter($this->variables, function (array $pair) use ($offset) {
            return $pair[0] === $offset;
        }));
    }

    public function offsetFor(Variable $target): ?int
    {
        foreach ($this->variables as [$offset, $variable]) {
            if ($target === $variable) {
                return $offset;
            }
        }

        return null;
    }

    public function lastOrNull(): ?Variable
    {
        $last = end($this->variables);

        if (!$last) {
            return null;
        }

        return $last[1];
    }
}
