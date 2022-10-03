<?php

namespace Phpactor\Tests\Benchmark;

use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PHPUnit\Framework\Assert;

/**
 * @BeforeMethods({"setUp"})
 * @Iterations(10)
 */
class CompleteBench extends BaseBenchCase
{
    public function setUp(): void
    {
        $this->workspace()->reset();
        $this->loadProject('PhpUnit');
    }

    public function benchComplete(): void
    {
        $output = $this->runCommand('complete tests/FoobarTest.php 145'); //145?
        Assert::assertStringContainsString('short_description:pub', $output);
    }
}
