<?php

namespace Phpactor\Tests\Benchmark;

use Phpactor\Tests\IntegrationTestCase;
use Phpactor\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @BeforeMethods({"setUp"})
 * @Iterations(10)
 */
class CompleteBench extends BaseBenchCase
{
    public function setUp()
    {
        $this->initWorkspace();
        $this->loadProject('PhpUnit');
    }

    public function benchComplete()
    {
        $this->runCommand([
            'command' => 'complete',
            'path' => 'tests/FoobarTest.php',
            'offset'=> 184
        ]);

    }
}
