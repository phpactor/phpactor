<?php

namespace Phpactor\Tests\Benchmark;

use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\OutputTimeUnit;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use PhpBench\Benchmark\Metadata\Annotations\Warmup;

/**
 * @Iterations(4)
 * @Revs(2)
 * @OutputTimeUnit("milliseconds", precision=3)
 * @Warmup(1)
 */
class BaseLineBench extends BaseBenchCase
{
    public function __construct()
    {
        parent::__construct(static::class);
    }
    public function benchVersion(): void
    {
        $this->runCommand('--version');
    }

    public function benchRpcEcho(): void
    {
        $this->runCommand('rpc', '{"action":"echo","parameters":{"message":"hello"}');
    }
}
