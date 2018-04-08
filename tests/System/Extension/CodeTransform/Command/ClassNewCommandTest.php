<?php

namespace Phpactor\Tests\System\Extension\CodeTransform\Command;

use Phpactor\Tests\System\SystemTestCase;

class ClassNewCommandTest extends SystemTestCase
{
    public function setUp()
    {
        $this->workspace()->reset();
        $this->loadProject('Animals');
    }

    /**
     * Application level smoke tests
     *
     * @dataProvider provideNewClass
     */
    public function testNewClass($command, $expected)
    {
        $process = $this->phpactor($command);
        $this->assertSuccess($process);

        $this->assertContains($expected, trim($process->getOutput()));
        $this->assertFileExists($this->workspaceDir() . '/lib/Badger/Teeth.php');
    }

    public function provideNewClass()
    {
        return [
            'New class' => [
                'class:new lib/Badger/Teeth.php --no-interaction --force',
                <<<'EOT'
src:lib/Badger/Teeth.php
EOT
            ],
        ];
    }
}
