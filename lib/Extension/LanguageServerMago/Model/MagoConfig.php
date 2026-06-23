<?php

namespace Phpactor\Extension\LanguageServerMago\Model;

final class MagoConfig
{
    public function __construct(
        private string $bin,
        private int $timeout,
        private ?string $config = null,
    ) {
    }

    public function bin(): string
    {
        return $this->bin;
    }

    /**
     * Maximum time in milliseconds to wait for a Mago run before giving up.
     */
    public function timeout(): int
    {
        return $this->timeout;
    }

    public function config(): ?string
    {
        return $this->config;
    }
}
