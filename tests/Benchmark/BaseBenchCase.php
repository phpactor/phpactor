<?php

namespace Phpactor\Tests\Benchmark;

use Phpactor\Tests\IntegrationTestCase;
use Symfony\Component\Process\Process;

class BaseBenchCase extends IntegrationTestCase
{
    protected function runCommand(string $command, ?string $stdin = null): string
    {
        if (!file_exists($this->workspaceDir())) {
            $this->workspace()->reset();
        }
        $process = Process::fromShellCommandline(__DIR__ . '/../../bin/phpactor ' . $command);
        $process->setInput($stdin);
        $process->setWorkingDirectory($this->workspaceDir());
        $process->run();
        return $process->getOutput();
    }
}
