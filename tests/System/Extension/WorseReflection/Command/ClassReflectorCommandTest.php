<?php

namespace Phpactor\Tests\System\Extension\WorseReflection\Command;

use Phpactor\Tests\System\SystemTestCase;

class ClassReflectorCommandTest extends SystemTestCase
{
    public function setUp(): void
    {
        $this->workspace()->reset();
        $this->loadProject('Animals');
    }

    /**
     * @testdox Test reflection
     */
    public function testReflectCommand(): void
    {
        $process = $this->phpactorFromStringArgs('class:reflect lib/Badger.php');
        $this->assertSuccess($process);
        $output = $process->getOutput();
        $this->assertStringContainsString('Animals\Badger', $output);
        $this->assertStringContainsString('methods', $output);
    }

    /**
     * @testdox Test for class
     */
    public function testReflectCommandWithClass(): void
    {
        $process = $this->phpactorFromStringArgs('class:reflect "Animals\\Badger"');
        $this->assertSuccess($process);
        $output = $process->getOutput();
        $this->assertStringContainsString('Animals\Badger', $output);
        $this->assertStringContainsString('methods', $output);
    }
}
