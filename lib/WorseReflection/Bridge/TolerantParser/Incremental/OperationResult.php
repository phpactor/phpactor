<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Incremental;

final class OperationResult
{
    public function __construct(
        public string $name,
        public bool $success = true,
        public ?string $reason = null
    ) {
    }

    public function fail(string $reason): self
    {
        return new self($this->name, false, $reason);
    }
}
