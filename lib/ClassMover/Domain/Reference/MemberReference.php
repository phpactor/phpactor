<?php

namespace Phpactor\ClassMover\Domain\Reference;

use Phpactor\ClassMover\Domain\Name\MemberName;
use Phpactor\ClassMover\Domain\Model\Class_;

class MemberReference
{
    private function __construct(
        private readonly MemberName $method,
        private readonly Position $position,
        private readonly ?Class_ $class = null
    ) {
    }

    public function __toString(): string
    {
        return sprintf(
            '[%s:%s] %s',
            $this->position->start(),
            $this->position->end(),
            (string) $this->method
        );
    }

    public static function fromMemberNameAndPosition(MemberName $method, Position $position): MemberReference
    {
        return new self($method, $position);
    }

    public static function fromMemberNamePositionAndClass(MemberName $method, Position $position, Class_ $class): MemberReference
    {
        return new self($method, $position, $class);
    }

    public function methodName(): MemberName
    {
        return $this->method;
    }

    public function position(): Position
    {
        return $this->position;
    }

    public function hasClass(): bool
    {
        return null !== $this->class;
    }

    public function withClass(Class_ $class): self
    {
        return new self($this->method, $this->position, $class);
    }

    public function class(): ?Class_
    {
        return $this->class;
    }
}
