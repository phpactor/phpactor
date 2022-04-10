<?php

namespace Phpactor\WorseReflection\Core\Inference;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<array-key,TypeAssertion>
 */
final class TypeAssertions implements IteratorAggregate
{
    /**
     * @var TypeAssertion[]
     */
    private array $typeAssertions;

    /**
     * @param TypeAssertion[] $typeAssertions
     */
    public function __construct(array $typeAssertions)
    {
        $this->typeAssertions = $typeAssertions;
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
}
