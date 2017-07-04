<?php

namespace Phpactor\Tests\UserInterface\Console\Command;

use Phpactor\Tests\UserInterface\SystemTestCase;

class FileInfoCommandTest extends SystemTestCase
{
    public function setUp()
    {
        $this->initWorkspace();
        $this->loadProject('Animals');
    }

    /**
     * @testdox It provides information about the file.
     */
    public function testProvideInformationForOffset()
    {
        $process = $this->phpactor('file:info lib/Badger.php');
        $this->assertSuccess($process);
        $this->assertContains('class:Animals\Badger', $process->getOutput());
    }

    /**
     * @testdox It provides information about the file as JSON
     */
    public function testProvideInformationForOffsetAsJson()
    {
        $process = $this->phpactor('file:info lib/Badger.php --format=json');
        $this->assertSuccess($process);
        $this->assertContains('{"class":"Animals', $process->getOutput());
    }
}
