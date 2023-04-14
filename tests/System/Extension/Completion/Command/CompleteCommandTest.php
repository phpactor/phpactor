<?php

namespace Phpactor\Tests\System\Extension\Completion\Command;

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
    public function testComplete($command, $expected): void
    {
        $process = $this->phpactorFromStringArgs($command);
        $this->assertSuccess($process);
        $this->assertStringContainsString($expected, trim($process->getOutput()));
    }

    public function provideComplete()
    {
        return [
            'Complete' => [
                'complete lib/Badger.php 181',
                <<<'EOT'
                    suggestions:
                    EOT
            ],
            'Complete with type' => [
                'complete lib/Badger.php 181 --type=cucumber',
                <<<'EOT'
                    suggestions:
                    EOT
            ],
        ];
    }
}
