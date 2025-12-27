<?php

namespace Phpactor\ClassMover\Domain\Reference;

use Phpactor\ClassMover\Domain\Name\Namespace_;

final class NamespaceReference
{
    private function __construct(
        private readonly Namespace_ $namespace,
        private readonly Position $position,
    ) {
    }

    public function __toString(): string
    {
        return (string) $this->namespace;
    }

    public static function fromNameAndPosition(Namespace_ $namespace, Position $position): self
    {
        return new self($namespace, $position);
    }

    public static function forRoot(): self
    {
        /** @var Namespace_ $rootNamespace */
        $rootNamespace = Namespace_::root();
        return new self($rootNamespace, Position::fromStartAndEnd(0, 0));
    }

    public function position(): Position
    {
        return $this->position;
    }

    public function namespace(): Namespace_
    {
        return $this->namespace;
    }
}
