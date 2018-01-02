<?php

namespace Phpactor\Tests\Benchmark;

use Phpactor\Tests\IntegrationTestCase;
use Phpactor\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @BeforeMethods({"setUp"})
 * @Iterations(10)
 */
class ClassSearchBench extends BaseBenchCase
{
    public function setUp()
    {
        $this->initWorkspace();
        $this->loadProject('Symfony');
    }

    public function benchClassSearch()
    {
        $this->runCommand([ 'command' => 'class:search', 'name' => 'Request' ]);
    }
}
