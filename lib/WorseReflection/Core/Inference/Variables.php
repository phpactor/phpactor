<?php

namespace Phpactor\WorseReflection\Core\Inference;

use ArrayIterator;
use IteratorAggregate;
use Phpactor\WorseReflection\Core\Types;
use Traversable;

/**
 * @implements IteratorAggregate<array-key, Variable>
 */
final class Variables implements IteratorAggregate
{
    /**
     * @var Variable[]
     */
    private array $variables;

    /**
     * @param Variable[] $variables
     */
    public function __construct(array $variables)
    {
        $this->variables = $variables;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->variables);
    }

    public function combine(): self
    {
        return new self(array_map(
            fn (Variable $v) => $v->withType(TypeCombinator::anihilate($v->type())),
            $this->variables
        ));
    }

    public function add(Variable $variable): self
    {
        $this->variables[$variable->name()] = $variable;
        return $this;
    }

    public function getOrCreate(string $string): Variable
    {
        foreach ($this->variables as $variable) {
            if ($variable->name() === $string) {
                return $variable;
            }
        }

        return new Variable($string, Types::empty());
    }
}
