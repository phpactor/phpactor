<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Countable;
use IteratorAggregate;
use RuntimeException;
use ArrayIterator;

/**
 * @implements IteratorAggregate<int,Variable>
 */
abstract class Assignments implements Countable, IteratorAggregate
{
    /**
     * @var Variable[]
     */
    private array $variables = [];

    /**
     * @param Variable[] $variables
     */
    protected function __construct(array $variables)
    {
        foreach ($variables as $variable) {
            $this->add($variable);
        }
    }

    public function add(Variable $variable): void
    {
        $this->variables[] = $variable;
    }

    /**
     * @return self
     */
    public function byName(string $name): Assignments
    {
        return new static(array_filter($this->variables, function (Variable $variable) use ($name) {
            return $variable->isNamed($name);
        }));
    }

    public function lessThanOrEqualTo(int $offset): Assignments
    {
        return new static(array_filter($this->variables, function (Variable $variable) use ($offset) {
            return $variable->offset()->toInt() <= $offset;
        }));
    }

    public function lessThan(int $offset): Assignments
    {
        return new static(array_filter($this->variables, function (Variable $variable) use ($offset) {
            return $variable->offset()->toInt() < $offset;
        }));
    }

    public function greaterThan(int $offset): Assignments
    {
        return new static(array_filter($this->variables, function (Variable $variable) use ($offset) {
            return $variable->offset()->toInt() > $offset;
        }));
    }

    public function greaterThanOrEqualTo(int $offset): Assignments
    {
        return new static(array_filter($this->variables, function (Variable $variable) use ($offset) {
            return $variable->offset()->toInt() >= $offset;
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

        return $first;
    }

    public function atIndex(int $index): Variable
    {
        if (!isset($this->variables[$index])) {
            throw new RuntimeException(sprintf(
                'No variable at index "%s"',
                $index
            ));
        }

        return $this->variables[$index];
    }

    public function last(): Variable
    {
        $last = end($this->variables);

        if (!$last) {
            throw new RuntimeException(
                'Cannot get last, variable collection is empty'
            );
        }

        return $last;
    }
    
    public function count(): int
    {
        return count($this->variables);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->variables);
    }

    public function merge(Assignments $variables): Assignments
    {
        foreach ($variables as $variable) {
            $this->variables[] = $variable;
        }

        return $this;
    }

    public function replace(Variable $existing, Variable $replacement): void
    {
        foreach ($this->variables as $index => $variable) {
            if ($variable !== $existing) {
                continue;
            }
            $this->variables[$index] = $replacement;
        }
    }

    public function equalTo(int $offset): Assignments
    {
        return new static(array_filter($this->variables, function (Variable $variable) use ($offset) {
            return $variable->offset()->toInt() === $offset;
        }));
    }
}
