<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Closure;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\MissingType;

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

    private ?Type $returnType = null;

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

    public function withReturnType(Type $type): self
    {
        $this->returnType = $type;
        return $this;
    }

    public function applyTypeAssertions(TypeAssertions $typeAssertions, int $contextOffset, ?int $createAtOffset = null): void
    {
        foreach ([
            [ $typeAssertions->properties(), $this->properties() ],
            [ $typeAssertions->variables(), $this->locals() ],
        ] as [ $typeAssertions, $frameVariables ]) {
            foreach ($typeAssertions as $typeAssertion) {
                $original = null;
                foreach ($frameVariables->byName($typeAssertion->name())->lessThanOrEqualTo($contextOffset) as $variable) {
                    $original = $variable;
                }
                $originalType = $original ? $original->type() : new MissingType();

                $variable = new Variable(
                    $typeAssertion->name(),
                    $createAtOffset  ?: $typeAssertion->offset(),
                    $typeAssertion->apply($originalType),
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

        // find any variables that were reassigned in the range
        foreach ($this->locals()->greaterThanOrEqualTo($before)->lessThanOrEqualTo($after) as $extra) {

            // if it was defined before then restore it
            if (isset($locals[$extra->name()])) {
                $this->locals()->add($locals[$extra->name()]->withOffset($after));
                continue;
            }

            // otherwise set the type to undefined
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

    public function returnType(): Type
    {
        return $this->returnType ?: new MissingType();
    }
}
