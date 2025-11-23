<?php

namespace Phpactor\Tests\System\Extension\WorseReflection\Command;

use PHPUnit\Framework\Attributes\TestDox;
use Phpactor\Tests\System\SystemTestCase;

class ClassReflectorCommandTest extends SystemTestCase
{
    public function setUp(): void
    {
        $this->workspace()->reset();
        $this->loadProject('Animals');
    }

    #[TestDox('Test reflection')]
    public function testReflectCommand(): void
    {
        $process = $this->phpactorFromStringArgs('class:reflect lib/Badger.php');
        $this->assertSuccess($process);
        $output = $process->getOutput();
        $this->assertStringContainsString('Animals\Badger', $output);
        $this->assertStringContainsString('methods', $output);
    }

    #[TestDox('Test for class')]
    public function testReflectCommandWithClass(): void
    {
        $process = $this->phpactorFromStringArgs('class:reflect "Animals\\Badger"');
        $this->assertSuccess($process);
        $output = $process->getOutput();
        $this->assertStringContainsString('Animals\Badger', $output);
        $this->assertStringContainsString('methods', $output);
    }
}
