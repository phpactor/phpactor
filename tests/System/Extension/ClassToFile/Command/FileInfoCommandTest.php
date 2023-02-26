<?php

namespace Phpactor\Tests\System\Extension\ClassToFile\Command;

use Phpactor\Tests\System\SystemTestCase;

class FileInfoCommandTest extends SystemTestCase
{
    public function setUp(): void
    {
        $this->workspace()->reset();
        $this->loadProject('Animals');
    }

    /**
     * @testdox It provides information about the file.
     */
    public function testProvideInformationForOffset(): void
    {
        $process = $this->phpactorFromStringArgs('file:info lib/Badger.php');
        $this->assertSuccess($process);
        $this->assertStringContainsString('class:Animals\Badger', $process->getOutput());
    }

    /**
     * @testdox It provides information about the file as JSON
     */
    public function testProvideInformationForOffsetAsJson(): void
    {
        $process = $this->phpactorFromStringArgs('file:info lib/Badger.php --format=json');
        $this->assertSuccess($process);
        $this->assertStringContainsString('{"class":"Animals', $process->getOutput());
    }
}
