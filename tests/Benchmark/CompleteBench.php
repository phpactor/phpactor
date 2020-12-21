<?php

namespace Phpactor\Tests\Benchmark;

use PHPUnit\Framework\Assert;

/**
 * @BeforeMethods({"setUp"})
 * @Iterations(10)
 */
class CompleteBench extends BaseBenchCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->loadProject('PhpUnit');
    }

    public function benchComplete()
    {
        $output = $this->runCommand('complete tests/FoobarTest.php 145'); //145?
        Assert::assertStringContainsString('info:pub', $output);
    }
}
