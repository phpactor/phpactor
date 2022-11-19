<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model;

final class PhpstanConfig
{
    public function __construct(private string $phpstanBin, private ?string $level = null)
    {
    }

    public function level(): ?string
    {
        return $this->level;
    }

    public function phpstanBin(): string
    {
        return $this->phpstanBin;
    }
}
