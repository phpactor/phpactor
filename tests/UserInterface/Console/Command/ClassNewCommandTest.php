<?php

namespace Phpactor\Tests\UserInterface\Console\Command;

use Phpactor\Tests\UserInterface\SystemTestCase;

class ClassNewCommandTest extends SystemTestCase
{
    public function setUp()
    {
        $this->initWorkspace();
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
path:/home/daniel/www/phpactor/phpactor/tests/Assets/Workspace/lib/Badger/Teeth.php
EOT
            ],
        ];
    }
}

