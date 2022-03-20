<?php

namespace Phpactor\WorseReflection\Core;

class Deprecation
{
    private ?string $message;
    
    private bool $isDefined;

    public function __construct(bool $isDefined, ?string $message = null)
    {
        $this->message = $message;
        $this->isDefined = $isDefined;
    }

    public function isDefined(): bool
    {
        return $this->isDefined;
    }

    public function message(): string
    {
        return $this->message;
    }
}
