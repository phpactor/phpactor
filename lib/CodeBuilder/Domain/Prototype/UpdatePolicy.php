<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class UpdatePolicy
{
    public function __construct(private bool $doUpdate)
    {
    }

    public static function fromModifiedState(bool $modified): self
    {
        return new self($modified);
    }

    public function applyUpdate(): bool
    {
        return $this->doUpdate;
    }

    public static function update(): UpdatePolicy
    {
        return new self(true);
    }
}
