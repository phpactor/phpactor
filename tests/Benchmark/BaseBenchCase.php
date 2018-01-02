<?php

namespace Phpactor\Tests\Benchmark;

use Phpactor\Tests\IntegrationTestCase;
use Phpactor\Console\Application;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\ArrayInput;

class BaseBenchCase extends IntegrationTestCase
{
    protected function runCommand(array $input)
    {
        chdir($this->workspaceDir());
        $application = new Application();
        $output = new BufferedOutput();
        $application->setAutoExit(false);
        $application->run(new ArrayInput($input), $output);
    }
}
