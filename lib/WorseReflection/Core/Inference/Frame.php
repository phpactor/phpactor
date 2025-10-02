<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Type;
use Stringable;

interface Frame extends Stringable
{
    public function __toString();

    public function new(): Frame;

    /**
     * @return Assignments<Variable>
     */
    public function locals(): Assignments;

    public function properties(): Assignments;

    public function problems(): Problems;

    public function parent(): ?Frame;

    public function root(): Frame;

    public function setReturnType(Type $type): self;

    public function applyTypeAssertions(TypeAssertions $typeAssertions, int $contextOffset, ?int $createAtOffset = null): void;
}
