<?php

namespace Phpactor\Tests\Benchmark;

/**
 * @BeforeMethods({"setUp"})
 * @Iterations(10)
 */
class StatusBench extends BaseBenchCase
{
    public function setUp()
    {
        $this->workspace()->reset();
        $this->loadProject('PhpUnit');
    }

    public function benchComplete()
    {
        $this->runCommand('status');
    }
}
