<?php

namespace Phpactor\Tests\System;

use Phpactor\Tests\IntegrationTestCase;
use Symfony\Component\Process\Process;

abstract class SystemTestCase extends IntegrationTestCase
{
    protected function phpactorFromStringArgs(string $args, ?string $stdin = null): Process
    {
        chdir($this->workspaceDir());

        $bin = __DIR__ . '/../../bin/phpactor --no-ansi --verbose ';
        $process = Process::fromShellCommandline(sprintf(
            '%s %s %s',
            PHP_BINARY,
            $bin,
            $args
        ), null, [
            'XDG_CACHE_HOME' => __DIR__ . '/../Assets/Cache',
            'PHPACTOR_UNCONDITIONAL_TRUST' => true,
        ]);

        if ($stdin) {
            $process->setInput($stdin);
        }

        $process->run();

        return $process;
    }
}
