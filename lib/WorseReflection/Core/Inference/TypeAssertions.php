<?php

namespace Phpactor\WorseReflection\Core\Inference;

use ArrayIterator;
use IteratorAggregate;
use Phpactor\WorseReflection\Core\TypeFactory;
use Traversable;

/**
 * @implements IteratorAggregate<array-key,TypeAssertion>
 */
final class TypeAssertions implements IteratorAggregate
{
    /**
     * @var TypeAssertion[]
     */
    private array $typeAssertions = [];

    /**
     * @param TypeAssertion[] $typeAssertions
     */
    public function __construct(array $typeAssertions)
    {
        foreach ($typeAssertions as $assertion) {
            $key = $this->key($assertion);
            $this->typeAssertions[$key] = $assertion;
        }
    }

    public function __toString(): string
    {
        return implode("\n", array_map(function (TypeAssertion $typeAssertion) {
            return sprintf(
                '%s: %s: %s',
                $typeAssertion->variableType(),
                $typeAssertion->name(),
                $typeAssertion->type()->__toString()
            );
        }, $this->typeAssertions));
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->typeAssertions);
    }

    /**
     * @param callable(TypeAssertion): TypeAssertion $closure
     */
    public function map(callable $closure): self
    {
        return new self(array_map($closure, $this->typeAssertions));
    }

    public function add(TypeAssertion $typeAssertion): self
    {
        $assertions = $this->typeAssertions;
        $assertions[] = $typeAssertion;
        return new self($assertions);
    }

    public function variables(): self
    {
        return new self(array_filter($this->typeAssertions, function (TypeAssertion $typeAssertion) {
            return $typeAssertion->variableType() === TypeAssertion::VARIABLE_TYPE_VARIABLE;
        }));
    }

    public function properties(): self
    {
        return new self(array_filter($this->typeAssertions, function (TypeAssertion $typeAssertion) {
            return $typeAssertion->variableType() === TypeAssertion::VARIABLE_TYPE_PROPERTY;
        }));
    }

    public function negate(): self
    {
        return $this->map(function (TypeAssertion $assertion) {
            return $assertion->withType(TypeFactory::not($assertion->type()));
        });
    }

    public function merge(TypeAssertions $typeAssertions): self
    {
        $assertions = $this->typeAssertions;
        foreach ($typeAssertions as $key => $assertion) {
            if (isset($assertions[$key])) {
                $assertions[$key] = $assertions[$key]->withType(TypeCombinator::add($assertions[$key]->type(), $assertion->type()));
                continue;
            }
            $assertions[$key] = $assertion;
        }

        return new self($assertions);
    }

    private function key(TypeAssertion $assertion): string
    {
        $key = $assertion->variableType().$assertion->name();
        return $key;
    }
}
