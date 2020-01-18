<?php

namespace Phpactor\Tests\System\Extension\CodeTransform\Command;

use Phpactor\Tests\System\SystemTestCase;

class FixCodeStyleCommandTest extends SystemTestCase
{
    public function setUp()
    {
        $this->workspace()->reset();
        $this->loadProject('Animals');
    }

    /**
     * Application level smoke tests
     *
     * @dataProvider provideSmokeSuccess
     */
    public function testSmokeSuccess($command)
    {
        $process = $this->phpactor($command);
        $this->assertSuccess($process);
    }

    public function provideSmokeSuccess()
    {
        yield 'No arguments' => [
            'style:fix lib/Badger.php',
        ];
    }
}
