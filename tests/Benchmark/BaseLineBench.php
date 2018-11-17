<?php

namespace Phpactor\Tests\Benchmark;

class BaseLineBench extends BaseBenchCase
{
    /**
     * @Iterations(10)
     * @Revs(10)
     * @OutputTimeUnit('ms')
     */
    public function benchVersion()
    {
        $this->runCommand('--version');
    }
}
