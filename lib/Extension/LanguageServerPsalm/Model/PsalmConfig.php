<?php

namespace Phpactor\Extension\LanguageServerPsalm\Model;

final class PsalmConfig
{
    public function __construct(private string $phpstanBin, private bool $shouldShowInfo)
    {
    }

    public function psalmBin(): string
    {
        return $this->phpstanBin;
    }

    public function shouldShowInfo(): bool
    {
        return $this->shouldShowInfo;
    }
}
