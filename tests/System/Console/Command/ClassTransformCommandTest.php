<?php

namespace Phpactor\Tests\System\Console\Command;

use Phpactor\Tests\System\SystemTestCase;

class ClassTransformCommandTest extends SystemTestCase
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
    public function testSmokeSuccess($command, array $fileMap = [], array $contentExpectations = [])
    {
        $process = $this->phpactor($command);
        $this->assertSuccess($process);
    }

    public function provideSmokeSuccess()
    {
        return [
            'Complete constructor' => [
                'class:transform lib/Badger/Carnivorous.php --transform=complete_constructor',
                [],
            ]
        ];
    }
}
