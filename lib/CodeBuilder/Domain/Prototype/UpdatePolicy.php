<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class UpdatePolicy
{
    private bool $doUpdate;

    public function __construct(bool $doUpdate)
    {
        $this->doUpdate = $doUpdate;
    }

    public static function fromModifiedState(bool $modified)
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
