<?php

namespace Phpactor\Tests\UserInterface\Console\Command;

use Phpactor\Tests\UserInterface\SystemTestCase;

class ReferencesMethodCommandTest extends SystemTestCase
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
        $process = $this->phpactor('references:method "Animals\Badger" badge');
        $this->assertSuccess($process);
        $this->assertContains('$this->⟶badge⟵', $process->getOutput());
    }

    /**
     * @testdox When non-existing method given, suggest existing methods with exception.
     */
    public function testNonExistingMethod()
    {
        $process = $this->phpactor('references:method "Animals\Badger" bad');
        $this->assertEquals(255, $process->getExitCode());
        $this->assertContains('known methods: "__construct"', $process->getErrorOutput());
    }

    /**
     * @testdox Find all methods for class
     */
    public function testFindAllForClass()
    {
        $process = $this->phpactor('references:method "Animals\Badger"');
        $this->assertSuccess($process);
    }

    /**
     * @testdox Find all methods
     */
    public function testFindAll()
    {
        $process = $this->phpactor('references:method');
        $this->assertSuccess($process);
    }
}

