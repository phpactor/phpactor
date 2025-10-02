<?php

namespace Phpactor\WorseReflection\Core\Inference\Frame;

use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\Assignments;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\Problems;
use Phpactor\WorseReflection\Core\Inference\TypeAssertions;
use Phpactor\WorseReflection\Core\Type;

class LazyFrame implements Frame
{
    private ?Frame $frame = null;

    public function __construct(private FrameResolver $frameResolver, private Node $node)
    {
    }

    public function __toString(): string
    {
        return $this->frame()->__toString();
    }

    public function new(): Frame
    {
        return $this->frame()->new();
    }

    public function locals(): Assignments
    {
        return $this->frame()->__toString();
    }

    public function properties(): Assignments
    {
        return $this->frame()->properties();
    }

    public function problems(): Problems
    {
        return $this->frame()->problems();
    }

    public function parent(): ?Frame
    {
        return $this->frame()->parent();
    }

    public function root(): Frame
    {
        return $this->frame()->root();
    }

    public function setReturnType(Type $type): self
    {
        return $this->frame()->setReturnType($type);
    }

    public function applyTypeAssertions(TypeAssertions $typeAssertions, int $contextOffset, ?int $createAtOffset = null): void
    {
        $this->frame()->applyTypeAssertions($typeAssertions, $contextOffset, $createAtOffset);
    }

    private function frame(): Frame
    {
        if (null !== $this->frame) {
            return $this->frame;
        }
        $this->frame = $this->frameResolver->build($this->node);
        return $this->frame;
    }
}
