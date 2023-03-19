<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Closure;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\VoidType;

class Frame
{
    private PropertyAssignments $properties;

    private LocalAssignments $locals;

    private Problems $problems;

    /**
     * @var Frame[]
     */
    private array $children = [];

    private ?Type $returnType = null;

    private int $version = 1;

    private VarDocBuffer $varDocBuffer;

    public function __construct(
        private string $name,
        LocalAssignments $locals = null,
        PropertyAssignments $properties = null,
        Problems $problems = null,
        private ?Frame $parent = null
    ) {
        $this->properties = $properties ?: PropertyAssignments::create();
        $this->locals = $locals ?: LocalAssignments::create();
        $this->problems = $problems ?: Problems::create();
        $this->varDocBuffer = new VarDocBuffer();
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

    public function setReturnType(Type $type): self
    {
        $this->returnType = $type;
        $this->version++;
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

                $frameVariables->set($variable);
            }
        }
    }

    public function returnType(): Type
    {
        return $this->returnType ?: new VoidType();
    }

    /**
     * The version is incremented when the frame or one of it's components is
     * modified and can be used for cache busting.
     */
    public function version(): string
    {
        return sprintf(
            '%s-%s-%s-%s',
            $this->locals()->version(),
            $this->properties()->version(),
            $this->varDocBuffer()->version(),
            $this->version
        );
    }

    public function varDocBuffer(): VarDocBuffer
    {
        return $this->varDocBuffer;
    }
}
