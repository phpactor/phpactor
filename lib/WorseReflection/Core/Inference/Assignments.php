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
    private int $version = 1;

    /**
     * @var array<string,Variable>
     */
    private array $variables = [];

    /**
     * @var array<string,array<string,Variable>>
     */
    private array $variablesByName = [];

    /**
     * @param array<string,Variable> $variables
     */
    final protected function __construct(array $variables)
    {
        $this->variables = $variables;
    }


    public function __toString(): string
    {
        return implode("\n", array_map(function (Variable $variable) {
            return sprintf(
                '%s:%s: %s',
                $variable->name(),
                $variable->offset(),
                $variable->type()->__toString()
            );
        }, array_values($this->variables)));
    }

    public function set(Variable $variable): void
    {
        $this->version++;
        $this->variables[$variable->key()] = $variable;
        $this->variablesByName[$variable->name()][$variable->key()] = $variable;
    }

    public function add(Variable $variable, int $offset): void
    {
        $this->version++;
        $original = $this->byName($variable->name())->lessThanOrEqualTo($offset)->lastOrNull();
        if ($original === null) {
            $this->set($variable);
            return;
        }
        $this->set($variable->withOffset(
            $variable->offset()
        )->withType($original->type()->addType($variable->type())->clean()));
    }

    /**
     * Return all variables matching the given name.
     *
     * When this method is used on the original frame it will return directly,
     * if used after other filters it will filter over all variables which can
     * be slow.
     *
     * IMPORTANT: Call this method BEFORE calling greater than / less than etc.
     */
    public function byName(string $name): Assignments
    {
        $name = ltrim($name, '$');

        // best case
        if (isset($this->variablesByName[$name])) {
            return new static($this->variablesByName[$name]);
        }

        // worst case
        return new static(array_filter($this->variables, function (Variable $v) use ($name) {
            return $v->name() === $name;
        }));
    }

    public function lessThanOrEqualTo(int $offset): Assignments
    {
        return new static(array_filter($this->variables, function (Variable $v) use ($offset) {
            return $v->offset() <= $offset;
        }));
    }

    public function lessThan(int $offset): Assignments
    {
        return new static(array_filter($this->variables, function (Variable $v) use ($offset) {
            return $v->offset() < $offset;
        }));
    }

    public function greaterThan(int $offset): Assignments
    {
        return new static(array_filter($this->variables, function (Variable $v) use ($offset) {
            return $v->offset() > $offset;
        }));
    }

    public function greaterThanOrEqualTo(int $offset): Assignments
    {
        return new static(array_filter($this->variables, function (Variable $v) use ($offset) {
            return $v->offset() >= $offset;
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
        $variables = array_values($this->variables);
        if (!isset($variables[$index])) {
            throw new RuntimeException(sprintf(
                'No variable at index "%s"',
                $index
            ));
        }

        return $variables[$index];
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

    /**
     * @return ArrayIterator<array-key,Variable>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator(array_values($this->variables));
    }

    public function merge(Assignments $variables): void
    {
        foreach ($variables->variables as $key => $variable) {
            $this->variables[$key] = $variable;
        }
    }

    public function replace(Variable $existing, Variable $replacement): void
    {
        foreach ($this->variables as $key => $variable) {
            if ($variable !== $existing) {
                continue;
            }
            $this->version++;
            $this->variables[$key] = $replacement;
            foreach ($this->variablesByName[$replacement->name()] ?? [] as $key => $byName) {
                if ($byName !== $existing) {
                    continue;
                }
                $this->variablesByName[$replacement->name()][$key] = $replacement;
            }
        }
    }

    public function equalTo(int $offset): Assignments
    {
        return new static(array_filter($this->variables, function (Variable $v) use ($offset) {
            return $v->offset() === $offset;
        }));
    }

    public function not(int $offset): Assignments
    {
        return new static(array_filter($this->variables, function (Variable $v) use ($offset) {
            return $v->offset() !== $offset;
        }));
    }

    public function assignmentsOnly(): Assignments
    {
        return new static(array_filter($this->variables, function (Variable $v) {
            return $v->wasAssigned();
        }));
    }

    public function definitionsOnly(): Assignments
    {
        return new static(array_filter($this->variables, function (Variable $v) {
            return $v->wasDefinition();
        }));
    }

    public function lastOrNull(): ?Variable
    {
        $last = end($this->variables);

        if (!$last) {
            return null;
        }

        return $last;
    }

    public function version(): int
    {
        return $this->version;
    }

    public function mostRecent(): self
    {
        $mostRecent = [];
        foreach ($this->variables as $variable) {
            $mostRecent[$variable->name()] = $variable;
        }

        return new static($mostRecent);
    }

    /**
     * @return Variable[]
     */
    public function toArray(): array
    {
        return $this->variables;
    }
}
