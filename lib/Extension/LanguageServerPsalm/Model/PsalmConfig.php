<?php

namespace Phpactor\Extension\LanguageServerPsalm\Model;

final class PsalmConfig
{
    public function __construct(
        private readonly string $phpstanBin,
        private readonly bool $shouldShowInfo,
        private readonly bool $useCache,
        private readonly ?int $errorLevel = null,
        private readonly ?int $threads = null,
        private readonly ?string $config = null,
    ) {
    }

    public function threads(): ?int
    {
        return $this->threads;
    }

    public function psalmBin(): string
    {
        return $this->phpstanBin;
    }

    public function shouldShowInfo(): bool
    {
        return $this->shouldShowInfo;
    }

    public function useCache(): bool
    {
        return $this->useCache;
    }

    public function errorLevel(): ?int
    {
        return $this->errorLevel;
    }

    public function config(): ?string
    {
        return $this->config;
    }
}
