<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Exception;

use RuntimeException;

class PhpCsFixerError extends RuntimeException
{
    public function __construct(int $exitCode, string $command, string $stderr, string $stdout)
    {
        parent::__construct(
            sprintf(
                "php-cs-fixer exited with code '%s'; cmd: %s; stderr: '%s'; stdout: '%s'",
                $exitCode,
                $command,
                $stderr,
                $stdout
            )
        );
    }
}
