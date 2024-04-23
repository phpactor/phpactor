<?php

namespace Phpactor\Tests\System\Extension\Completion\Command;

use Generator;
use Phpactor\Tests\System\SystemTestCase;

class CompleteCommandTest extends SystemTestCase
{
    public function setUp(): void
    {
        $this->workspace()->reset();
        $this->loadProject('Animals');
    }

    /**
     * @dataProvider provideComplete
     */
    public function testComplete(string $command, string $expected): void
    {
        $process = $this->phpactorFromStringArgs($command);
        $this->assertSuccess($process);
        $this->assertStringContainsString($expected, trim($process->getOutput()));
    }

    /**
     * @return Generator<string,array{string,string}>
     */
    public function provideComplete(): Generator
    {
        yield 'Complete' => [
            'complete lib/Badger.php 181',
            <<<'EOT'
                suggestions:
                EOT
        ];
        yield 'Complete with type' => [
            'complete lib/Badger.php 181 --type=cucumber',
            <<<'EOT'
                suggestions:
                EOT
        ];
    }
}
