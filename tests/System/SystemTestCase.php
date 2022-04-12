<?php

namespace Phpactor\Tests\System;

use Phpactor\Tests\IntegrationTestCase;
use Symfony\Component\Process\Process;

abstract class SystemTestCase extends IntegrationTestCase
{
    protected function phpactor(string $args, string $stdin = null): Process
    {
        chdir($this->workspaceDir());

        $bin = __DIR__ . '/../../bin/phpactor --verbose ';
        $process = Process::fromShellCommandline(sprintf(
            '%s %s',
            $bin,
            $args
        ), null, [
            'XDG_CACHE_HOME' => $this->workspaceDir(),
        ]);

        if ($stdin) {
            $process->setInput($stdin);
        }

        $process->run();

        return $process;
    }
}
