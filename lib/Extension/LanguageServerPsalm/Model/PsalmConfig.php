<?php

namespace Phpactor\Extension\LanguageServerPsalm\Model;

final class PsalmConfig
{
    private string $phpstanBin;

    public function __construct(string $phpstanBin)
    {
        $this->phpstanBin = $phpstanBin;
    }

    public function psalmBin(): string
    {
        return $this->phpstanBin;
    }
}
