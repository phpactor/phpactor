<?php

namespace Phpactor\Tests\Benchmark;

/**
 * @Iterations(4)
 * @Revs(2)
 * @OutputTimeUnit("milliseconds", precision=3)
 * @Warmup(1)
 */
class BaseLineBench extends BaseBenchCase
{
    public function benchVersion()
    {
        $this->runCommand('--version');
    }

    public function benchRpcEcho()
    {
        $this->runCommand('rpc', '{"action":"echo","parameters":{"message":"hello"}');
    }
}
