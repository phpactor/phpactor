<?php

namespace Phpactor\Tests\UserInterface\Console\Command;

use Phpactor\Tests\UserInterface\SystemTestCase;

class ClassReferencesCommandTest extends SystemTestCase
{
    public function setUp()
    {
        $this->initWorkspace();
        $this->loadProject('Animals');
    }

    /**
     * @testdox It should show all references to Badger
     */
    public function testReferences()
    {
        $process = $this->phpactor('class:references "Animals\Badger"');
        $this->assertSuccess($process);
        $this->assertContains('class Badger', $process->getOutput());
    }

    /**
     * @testdox It should accept a format
     */
    public function testReferencesFormatted()
    {
        $process = $this->phpactor('class:references "Animals\Badger" --format=json');
        $this->assertSuccess($process);
        $this->assertContains('"line":"class Badger', $process->getOutput());
    }
}

