<?php

namespace Phpactor\WorseReflection\Core\Inference\Frame;

use Phpactor\WorseReflection\Core\Inference\TypeAssertions;
use Phpactor\WorseReflection\Core\Inference\Assignments;
use Phpactor\WorseReflection\Core\Inference\VarDocBuffer;
use Phpactor\WorseReflection\Core\Inference\Problems;
use Phpactor\WorseReflection\Core\Inference\LocalAssignments;
use Phpactor\WorseReflection\Core\Inference\PropertyAssignments;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\VoidType;

class ConcreteFrame implements Frame
{
    private PropertyAssignments $properties;

    private LocalAssignments $locals;

    private Problems $problems;

    private ?Type $returnType = null;

    private VarDocBuffer $varDocBuffer;

    public function __construct(
        ?LocalAssignments $locals = null,
        ?PropertyAssignments $properties = null,
        ?Problems $problems = null,
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

    public function new(): Frame
    {
        $frame = new self(null, null, null, $this);

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

    public function parent(): ?Frame
    {
        return $this->parent;
    }

    public function root(): Frame
    {
        if (null === $this->parent) {
            return $this;
        }

        return $this->parent->root();
    }

    public function setReturnType(Type $type): Frame
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

                $frameVariables->set($variable);
            }
        }
    }

    public function returnType(): Type
    {
        return $this->returnType ?: new VoidType();
    }

    public function varDocBuffer(): VarDocBuffer
    {
        return $this->varDocBuffer;
    }
}
