<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Closure;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\UnionType;

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

    public function __toString(): string
    {
        return implode("\n", array_map(function (Assignments $assignments, string $type) {
            return $type ."\n:" . $assignments->__toString();
        }, [$this->properties, $this->locals], ['properties', 'locals']));
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

    public function applyTypeAssertions(TypeAssertions $typeAssertions, int $offset, bool $createNew = false): void
    {
        foreach ([
            [ $typeAssertions->properties(), $this->properties() ],
            [ $typeAssertions->variables(), $this->locals() ],
        ] as [ $typeAssertions, $frameVariables ]) {
            foreach ($typeAssertions as $typeAssertion) {
                $original = null;
                foreach ($frameVariables->byName($typeAssertion->name())->lessThanOrEqualTo(
                    $createNew ? $offset : $typeAssertion->offset()
                ) as $variable) {
                    $original = $variable;
                }
                $variable = new Variable(
                    $typeAssertion->name(),
                    $createNew  ? $offset : $typeAssertion->offset(),
                    UnionType::toUnion($typeAssertion->apply(
                        $original ? $original->type() : new MissingType(),
                    ))->reduce(),
                    $typeAssertion->classType(),
                );

                $type = $variable->type();

                $frameVariables->add($variable);
            }
        }
    }

    public function restoreToStateBefore(int $before, int $after): void
    {
        $locals = [];
        // get most recent state of variables before offset
        foreach ($this->locals()->lessThan($before) as $local) {
            $locals[$local->name()] = $local;
        }
        foreach ($locals as $local) {
            $this->locals()->add($local->withOffset($after));
        }

        foreach ($this->locals()->greaterThanOrEqualTo($before)->lessThanOrEqualTo($after) as $extra) {
            if (isset($locals[$extra->name()])) {
                continue;
            }

            // if variable was not present before $before, assign as missing
            $this->locals()->add($extra->withType(TypeFactory::undefined())->withOffset($after));
        }

        $properties = [];
        foreach ($this->properties() as $property) {
            $properties[$property->name()] = $property;
        }
        foreach ($properties as $property) {
            $this->properties()->add($property);
        }
    }
}
