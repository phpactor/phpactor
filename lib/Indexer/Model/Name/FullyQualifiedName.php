<?php

namespace Phpactor\Indexer\Model\Name;

class FullyQualifiedName
{
    public function __construct(private readonly string $fqn)
    {
    }

    public function __toString(): string
    {
        return $this->fqn;
    }

    public static function fromString(string $fqn): self
    {
        return new self($fqn);
    }

    public function head(): self
    {
        $id = $this->fqn;
        $offset = strrpos($id, '\\');

        if (false !== $offset) {
            $id = substr($id, $offset + 1);
        }

        return new self($id);
    }
}
