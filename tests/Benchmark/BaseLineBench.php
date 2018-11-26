<?php

namespace Phpactor\Tests\Benchmark;

class BaseLineBench extends BaseBenchCase
{
    /**
     * @Iterations(10)
     * @Revs(10)
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function benchVersion()
    {
        $this->runCommand('--version');
    }
}
