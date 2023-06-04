<?php

namespace Phpactor\WorseReflection\Core;

class Deprecation
{
    public function __construct(
        private bool $isDefined,
        private ?string $message = null,
        private ?string $replacementSuggestion = null,
    ) {
    }

    public function withMessage(?string $message): self
    {
        return new self(true, $message, $this->replacementSuggestion);
    }

    public function withReplacement(?string $replacement): self
    {
        return new self($this->isDefined, $this->message, $replacement);
    }

    public function isDefined(): bool
    {
        return $this->isDefined;
    }

    public function replacementSuggestion(): ?string
    {
        return $this->replacementSuggestion;
    }

    public function message(): string
    {
        return $this->message ?? '';
    }
}
