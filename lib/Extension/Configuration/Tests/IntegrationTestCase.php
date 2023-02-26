<?php

namespace Phpactor\Extension\Configuration\Tests;

use PHPUnit\Framework\TestCase;
use Phpactor\TestUtils\Workspace;
use Symfony\Component\Process\Process;

class IntegrationTestCase extends TestCase
{
    public function workspace(): Workspace
    {
        return new Workspace(__DIR__ . '/Workspace');
    }
    /**
     * @param list<string> $cmd
     */
    public function process(array $cmd): Process
    {
        $p = new Process(array_merge(
            [__DIR__ . '/../../../../bin/phpactor'],
            $cmd
        ), $this->workspace()->path());

        return $p;
    }
}
