<?php

namespace Phpactor\WorseReflection\Core\Inference;

use ArrayIterator;
use Closure;
use IteratorAggregate;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\UnionType;
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
            return $typeAssertion->__toString();
        }, $this->typeAssertions));
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->typeAssertions);
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
            $assertion->negate();
            return $assertion;
        });
    }

    public function map(Closure $closure): self
    {
        return new self(array_map($closure, $this->typeAssertions));
    }

    public function merge(TypeAssertions $typeAssertions): self
    {
        $assertions = $this->typeAssertions;
        foreach ($typeAssertions as $key => $assertion) {
            $assertions[$key] = $assertion;
        }

        return new self($assertions);
    }

    /**
     * Return union of the two type assertions
     * (e.g. $left || $right)
     */
    public function union(TypeAssertions $typeAssertions): self
    {
        $resolved = [];
        foreach ($this->typeAssertions as $typeAssertion) {
            $resolved[$typeAssertion->name()] = $typeAssertion;
        }

        foreach ($typeAssertions as $typeAssertion) {
            if (!isset($resolved[$typeAssertion->name()])) {
                $resolved[$typeAssertion->name()] = $typeAssertion;
                continue;
            }

            $left = $resolved[$typeAssertion->name()];
            $right = $typeAssertion;
            $resolved[$typeAssertion->name()] = TypeAssertion::variable(
                $typeAssertion->name(),
                $typeAssertion->offset(),
                fn (Type $type) => UnionType::fromTypes($left->apply($type), $right->apply($type)),
                function (Type $type) use ($left, $right) {
                    $type = $left->negate()->apply($type);
                    $type = $right->negate()->apply($type);
                    return $type;
                }
            );
        }

        return new self($resolved);
    }

    private function key(TypeAssertion $assertion): string
    {
        $key = $assertion->variableType().$assertion->name().$assertion->offset();
        return $key;
    }
}
