<?php

namespace Phpactor\Indexer\Model\Name;

class FullyQualifiedName
{
    /**
     * @var string
     */
    private $fqn;

    public function __construct(string $fqn)
    {
        $this->fqn = $fqn;
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
