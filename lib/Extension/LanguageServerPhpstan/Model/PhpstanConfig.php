<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model;

final class PhpstanConfig
{
    public function __construct(private string $phpstanBin, private ?string $level = null, private ?string $config = null, private ?string $memLimit = null)
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

    public function config(): ?string
    {
        return $this->config;
    }

    public function memLimit(): ?string
    {
        return $this->memLimit;
    }
}
