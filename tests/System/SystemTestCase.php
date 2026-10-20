<?php

namespace Phpactor\Tests\System;

use Phpactor\Tests\IntegrationTestCase;
use Symfony\Component\Process\Process;

abstract class SystemTestCase extends IntegrationTestCase
{
    /**
     * @param array<string,string|false> $env
     */
    protected function phpactorFromStringArgs(string $args, ?string $stdin = null, array $env = []): Process
    {
        chdir($this->workspaceDir());

        $bin = __DIR__ . '/../../bin/phpactor --no-ansi --verbose ';
        $process = Process::fromShellCommandline(sprintf(
            '%s %s %s',
            PHP_BINARY,
            $bin,
            $args
        ), null, array_merge([
            'XDG_CACHE_HOME' => __DIR__ . '/../Assets/Cache',
            'XDG_DATA_HOME' => $this->workspace()->path(),
            'PHPACTOR_UNCONDITIONAL_TRUST' => true,
        ], $env));

        if ($stdin) {
            $process->setInput($stdin);
        }

        $process->run();

        return $process;
    }
}
