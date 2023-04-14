<?php

namespace Phpactor\ClassMover\Domain\Name;

use InvalidArgumentException;

class QualifiedName
{
    /**
     * @param non-empty-array<string> $parts
     */
    protected function __construct(protected array $parts)
    {
    }

    public function __toString(): string
    {
        return implode('\\', $this->parts);
    }

    public static function root(): QualifiedName
    {
        return new static([]);
    }

    public function isEqualTo(QualifiedName $name): bool
    {
        return $name->__toString() == $this->__toString();
    }

    public static function fromString(string $string): static
    {
        if (empty($string)) {
            throw new InvalidArgumentException(
                'Name cannot be empty'
            );
        }

        /** @var non-empty-array<string> $parts */
        $parts = explode('\\', trim($string));

        return new static($parts);
    }

    public function base(): string
    {
        return reset($this->parts);
    }

    public function parentNamespace(): static
    {
        $parts = $this->parts;
        array_pop($parts);

        return new static($parts);
    }

    public function equals(QualifiedName $qualifiedName): bool
    {
        return $qualifiedName->__toString() == $this->__toString();
    }

    public function head(): string
    {
        return end($this->parts);
    }

    public function transpose(QualifiedName $name): self
    {
        // both fully qualified names? great, nothing to see here.
        if ($this instanceof FullyQualifiedName && $name instanceof FullyQualifiedName) {
            return $name;
        }

        // pretty sure there are some holes in this logic..
        $newParts = [];
        $replaceParts = $name->parts();

        for ($index = 0; $index < count($this->parts); ++$index) {
            $newParts[] = array_pop($replaceParts);
        }

        return new self(array_reverse(array_filter($newParts)));
    }

    /**
     * @return string[]
     */
    public function parts(): array
    {
        return $this->parts;
    }

    public function isAlone(): bool
    {
        return count($this->parts) === 1;
    }
}
