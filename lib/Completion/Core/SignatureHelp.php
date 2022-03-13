<?php

namespace Phpactor\Completion\Core;

class SignatureHelp
{
    /**
     * @var SignatureInformation[]
     */
    private array $signatures;

    private int $activeSignature;

    private ?int $activeParameter;

    public function __construct(
        array $signatures,
        ?int $activeSignature = null,
        ?int $activeParameter = null
    ) {
        $this->signatures = $signatures;
        $this->activeSignature = $activeSignature;
        $this->activeParameter = $activeParameter;
    }

    public function activeParameter(): ?int
    {
        return $this->activeParameter;
    }

    public function activeSignature(): int
    {
        return $this->activeSignature;
    }

    public function signatures(): array
    {
        return $this->signatures;
    }
}
