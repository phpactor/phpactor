<?php

namespace Phpactor\Tests\Integration\Console\Command;

use Phpactor\Tests\Integration\SystemTestCase;

class FileInfoAtOffsetCommandTest extends SystemTestCase
{
    public function setUp()
    {
        $this->initWorkspace();
        $this->loadProject('Animals');
    }

    /**
     * @testdox It provides information about the thing under the cursor.
     */
    public function testProvideInformationForOffset()
    {
        $process = $this->phpactor('file:offset lib/Badger.php 137');
        $this->assertSuccess($process);
        $this->assertContains('type:Animals\Badger\Carnivorous', $process->getOutput());
        $this->assertContains('Badger/Carnivorous.php', $process->getOutput());
    }

    /**
     * @testdox It provides information about the thing under the cursor as JSON
     */
    public function testProvideInformationForOffsetAsJson()
    {
        $process = $this->phpactor('file:offset lib/Badger.php 137 --format=json');
        $this->assertSuccess($process);
        $this->assertContains('{"symbol":"Carnivorous', $process->getOutput());
    }
}
