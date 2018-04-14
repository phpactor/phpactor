<?php

namespace Phpactor\Tests\Benchmark;

use Phpactor\Tests\IntegrationTestCase;
use Phpactor\Application;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Process;

class BaseBenchCase extends IntegrationTestCase
{
    protected function runCommand(string $command): string
    {
        chdir($this->workspaceDir());

        $process = new Process(__DIR__ . '/../../bin/phpactor ' . $command);
        $process->setWorkingDirectory($this->workspaceDir());
        $process->run();
        return $process->getOutput();
    }
}
