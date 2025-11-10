<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model;

use Phpactor\LanguageServerProtocol\DiagnosticSeverity;

final class PhpstanConfig
{
    /**
     * @param DiagnosticSeverity::* $severity
     */
    public function __construct(private string $phpstanBin, private int $severity, private ?string $level = null, private ?string $config = null, private ?string $memLimit = null)
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

    /**
     * @return DiagnosticSeverity::*
     */
    public function severity(): int
    {
        return $this->severity;
    }
}
