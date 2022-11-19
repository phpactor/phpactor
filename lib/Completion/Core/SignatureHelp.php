<?php

namespace Phpactor\Completion\Core;

class SignatureHelp
{
    /**
     * @param SignatureInformation[] $signatures
     */
    public function __construct(
        private array $signatures,
        private ?int $activeSignature = null,
        private ?int $activeParameter = null
    ) {
    }

    public function activeParameter(): ?int
    {
        return $this->activeParameter;
    }

    public function activeSignature(): int
    {
        return $this->activeSignature;
    }

    /**
     * @return SignatureInformation[]
     */
    public function signatures(): array
    {
        return $this->signatures;
    }
}
