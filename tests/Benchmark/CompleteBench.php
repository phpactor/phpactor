<?php

namespace Phpactor\Tests\Benchmark;

use Phpactor\Tests\IntegrationTestCase;
use Phpactor\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use PHPUnit\Framework\Assert;

/**
 * @BeforeMethods({"setUp"})
 * @Iterations(10)
 */
class CompleteBench extends BaseBenchCase
{
    public function setUp()
    {
        $this->workspace()->reset();
        $this->loadProject('PhpUnit');
    }

    public function benchComplete()
    {
        $output = $this->runCommand([
            'command' => 'complete',
            'path' => 'tests/FoobarTest.php',
            'offset'=> 145
        ]);
        Assert::assertContains('info:pub', $output->fetch());
    }
}
