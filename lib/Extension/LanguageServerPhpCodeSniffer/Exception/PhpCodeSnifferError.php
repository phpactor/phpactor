<?php

namespace Phpactor\Extension\LanguageServerPhpCodeSniffer\Exception;

use RuntimeException;

class PhpCodeSnifferError extends RuntimeException
{
    public function __construct(int $exitCode, string $command, string $stderr, string $stdout)
    {
        parent::__construct(
            sprintf(
                "phpcs exited with code '%s'; cmd: %s; stderr: '%s'; stdout: '%s'",
                $exitCode,
                $command,
                $stderr,
                $stdout
            )
        );
    }
}
