<?php

namespace Phpactor\Tests\System\Extension\Completion\Command;

use Phpactor\Tests\System\SystemTestCase;

class CompleteCommandTest extends SystemTestCase
{
    public function setUp()
    {
        $this->workspace()->reset();
        $this->loadProject('Animals');
    }

    /**
     * @dataProvider provideComplete
     */
    public function testComplete($command, $expected)
    {
        $process = $this->phpactor($command);
        $this->assertSuccess($process);
        $this->assertContains($expected, trim($process->getOutput()));
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
