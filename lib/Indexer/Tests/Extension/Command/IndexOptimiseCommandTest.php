<?php

namespace Phpactor\Indexer\Tests\Extension\Command;

use Phpactor\Indexer\Tests\IntegrationTestCase;
use Symfony\Component\Process\Process;

class IndexOptimiseCommandTest extends IntegrationTestCase
{
    public function tearDown():void
    {
        $this->workspace()->reset();
    }

    public function testRefreshIndex(): void
    {
        $this->initProject();

        // create an index
        $process = new Process([
            PHP_BINARY,
            __DIR__ . '/../../bin/console',
            'index:build',
        ], $this->workspace()->path());
        $process->mustRun();

        // optimise the index
        $process = new Process([
            PHP_BINARY,
            __DIR__ . '/../../bin/console',
            'index:optimise',
        ], $this->workspace()->path());
        $process->mustRun();

        self::assertEquals(0, $process->getExitCode());
        self::assertStringContainsString('optimisations done', $process->getOutput());
    }
}
