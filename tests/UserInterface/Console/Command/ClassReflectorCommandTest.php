<?php

namespace Phpactor\Tests\UserInterface\Console\Command;

use Phpactor\Tests\UserInterface\SystemTestCase;

class ClassReflectorCommandTest extends SystemTestCase
{
    public function setUp()
    {
        $this->initWorkspace();
        $this->loadProject('Animals');
    }

    /**
     * @testdox Test reflection
     */
    public function testReflectCommand()
    {
        $process = $this->phpactor('class:reflect lib/Badger.php');
        $this->assertSuccess($process);
        $output = $process->getOutput();
        $this->assertContains('Animals\Badger', $output);
        $this->assertContains('Methods', $output);
    }

    /**
     * @testdox Test for class
     */
    public function testReflectCommandWithClass()
    {
        $process = $this->phpactor('class:reflect "Animals\Badger"');
        $this->assertSuccess($process);
        $output = $process->getOutput();
        $this->assertContains('Animals\Badger', $output);
        $this->assertContains('Methods', $output);
    }
}
