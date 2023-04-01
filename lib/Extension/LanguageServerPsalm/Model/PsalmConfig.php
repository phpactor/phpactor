<?php

namespace Phpactor\Extension\LanguageServerPsalm\Model;

final class PsalmConfig
{
    public function __construct(
        private string $phpstanBin,
        private bool $shouldShowInfo,
        private bool $useCache
    ) {
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
}
