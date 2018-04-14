<?php

namespace Phpactor\Tests\Benchmark;

/**
 * @BeforeMethods({"setUp"})
 * @Iterations(10)
 */
class ClassSearchBench extends BaseBenchCase
{
    public function setUp()
    {
        $this->workspace()->reset();
        $this->loadProject('Symfony');
    }

    public function benchClassSearch()
    {
        $this->runCommand('class:search Request');
    }
}
