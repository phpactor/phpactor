<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Closure;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\TypeUtil;

class Frame
{
    private PropertyAssignments $properties;
    
    private LocalAssignments $locals;
    
    private Problems $problems;

    /**
     * @var Frame
     */
    private ?Frame $parent = null;

    /**
     * @var Frame[]
     */
    private array $children = [];
    
    private string $name;

    public function __construct(
        string $name,
        LocalAssignments $locals = null,
        PropertyAssignments $properties = null,
        Problems $problems = null,
        Frame $parent = null
    ) {
        $this->properties = $properties ?: PropertyAssignments::create();
        $this->locals = $locals ?: LocalAssignments::create();
        $this->problems = $problems ?: Problems::create();
        $this->parent = $parent;
        $this->name = $name;
    }

    public function new(string $name): Frame
    {
        $frame = new self($name, null, null, null, $this);
        $this->children[] = $frame;

        return $frame;
    }

    /**
     * @return Assignments<Variable>
     */
    public function locals(): Assignments
    {
        return $this->locals;
    }

    public function properties(): Assignments
    {
        return $this->properties;
    }

    public function problems(): Problems
    {
        return $this->problems;
    }

    public function parent(): Frame
    {
        return $this->parent;
    }

    public function reduce(Closure $closure, $initial = null)
    {
        $initial = $closure($this, $initial);

        foreach ($this->children as $childFrame) {
            $initial = $childFrame->reduce($closure, $initial);
        }

        return $initial;
    }

    public function root()
    {
        if (null === $this->parent) {
            return $this;
        }

        return $this->parent->root();
    }

    public function children(): array
    {
        return $this->children;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function withLocals(LocalAssignments $locals): self
    {
        $new = clone $this;
        $new->locals = $locals;

        return $new;
    }

    public function applyTypeAssertions(TypeAssertions $typeAssertions, int $offset): void
    {
        foreach ($typeAssertions->variables() as $typeAssertion) {
            $original = $this->locals()->byName($typeAssertion->name())->lastOrNull();
            $originalType = $original ? $original->type() : TypeFactory::undefined();
            $variable = new Variable($typeAssertion->name(), TypeUtil::applyType($originalType, $typeAssertion->type()));
            $this->locals()->add($offset, $variable);
        }

        foreach ($typeAssertions->properties() as $typeAssertion) {
            $original = $this->properties()->byName($typeAssertion->name())->lastOrNull();
            $originalType = $original ? $original->type() : TypeFactory::undefined();
            $variable = new Variable(
                $typeAssertion->name(),
                TypeUtil::applyType($originalType, $typeAssertion->type()),
                $typeAssertion->classType(),
            );
            $this->properties()->add($offset, $variable);
        }
    }
}
