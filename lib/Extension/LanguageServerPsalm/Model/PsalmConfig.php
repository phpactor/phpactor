<?php

namespace Phpactor\Extension\LanguageServerPsalm\Model;

final class PsalmConfig
{
    public function __construct(private string $phpstanBin)
    {
    }

    public function psalmBin(): string
    {
        return $this->phpstanBin;
    }
}
