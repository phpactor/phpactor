<?php

namespace Phpactor\Tests\Benchmark;

/**
 * @BeforeMethods({"setUp"})
 * @Iterations(10)
 */
class ClassSearchBench extends BaseBenchCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->loadProject('Symfony');
    }

    public function benchClassSearch()
    {
        $this->runCommand('class:search Request');
    }
}
