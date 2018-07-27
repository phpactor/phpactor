<?php

namespace Phpactor\Tests\System;

use Phpactor\Tests\IntegrationTestCase;
use Symfony\Component\Process\Process;

abstract class SystemTestCase extends IntegrationTestCase
{
    protected function phpactor(string $args, string $stdin = null, bool $async = false): Process
    {
        chdir($this->workspaceDir());
        $bin = __DIR__ . '/../../bin/phpactor --verbose ';
        $process = new Process(sprintf(
            '%s %s',
            $bin,
            $args
        ));

        if ($stdin) {
            $process->setInput($stdin);
        }

        $async ? $process->start() : $process->run();

        return $process;
    }
}
