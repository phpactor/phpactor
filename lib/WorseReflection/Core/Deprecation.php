<?php

namespace Phpactor\WorseReflection\Core;

class Deprecation
{
    public function __construct(
        private readonly bool $isDefined,
        private readonly ?string $message = null
    ) {
    }

    public function isDefined(): bool
    {
        return $this->isDefined;
    }

    public function message(): string
    {
        return $this->message ?? '';
    }
}
