<?php

namespace Phpactor\Name;

final class FullyQualifiedName implements Name
{
    private function __construct(private QualifiedName $qualifiedName)
    {
    }

    public function __toString(): string
    {
        return $this->qualifiedName->__toString();
    }

    public static function fromArray(array $parts): self
    {
        return new self(QualifiedName::fromArray($parts));
    }

    public static function fromString(string $string): self
    {
        return new self(QualifiedName::fromString($string));
    }

    public static function fromQualifiedName(QualifiedName $qualfifiedName): self
    {
        return new self($qualfifiedName);
    }

    /**
     * Reutrn the last element of the name (e.g. the class's short name)
     */
    public function head(): QualifiedName
    {
        return $this->qualifiedName->head();
    }

    /**
     * Return the "namespace" portion of the name.
     *
     * @return FullyQualifiedName
     */
    public function tail(): Name
    {
        return new self($this->qualifiedName->tail());
    }

    /**
     * @return FullyQualifiedName
     */
    public function prepend(Name $name): Name
    {
        return new self($this->qualifiedName->prepend($name));
    }

    /**
     * @return FullyQualifiedName
     */
    public function append(Name $name): Name
    {
        return new self($this->qualifiedName->append($name));
    }

    public function isDescendantOf(Name $name): bool
    {
        return $this->qualifiedName->isDescendantOf($name);
    }

    public function toArray(): array
    {
        return $this->qualifiedName->toArray();
    }

    public function count(): int
    {
        return $this->qualifiedName->count();
    }
}
