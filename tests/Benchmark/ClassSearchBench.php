<?php

namespace Phpactor\Tests\Benchmark;

use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;

/**
 * @BeforeMethods({"setUp"})
 * @Iterations(10)
 */
class ClassSearchBench extends BaseBenchCase
{
    public function __construct()
    {
        parent::__construct(static::class);
    }
    public function setUp(): void
    {
        $this->workspace()->reset();
        $this->loadProject('Symfony');
    }

    public function benchClassSearch(): void
    {
        $this->runCommand('class:search Request');
    }
}
