<?php

namespace Phpactor\Indexer\Model\Record;

trait HasFlagsTrait
{
    private int $flags = 0;

    public function setFlags(int $flags): self
    {
        $this->flags = $flags;

        return $this;
    }

    public function addFlag(int $flag): self
    {
        $this->flags = $this->flags | $flag;

        return $this;
    }

    public function hasFlag(int $flag): bool
    {
        return (bool) ($this->flags & $flag);
    }

    public function flags(): int
    {
        return $this->flags;
    }
}
