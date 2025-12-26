<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model;

use Phpactor\LanguageServerProtocol\DiagnosticSeverity;

final class PhpstanConfig
{
    /**
     * @param DiagnosticSeverity::* $severity
     */
    public function __construct(
        private readonly string $phpstanBin,
        private readonly int $severity,
        private readonly ?string $level = null,
        private readonly ?string $config = null,
        private readonly ?string $memLimit = null
    ) {
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
