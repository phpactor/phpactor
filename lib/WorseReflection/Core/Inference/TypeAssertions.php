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
     * @param callable(TypeAssertion $assertion): TypeAssertion
     */
    public function map(callable $closure): self
    {
        return new self(array_map($closure, $this->typeAssertions));
    } 
}
