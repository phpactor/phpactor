<?php

namespace Phpactor\Tests\Integration\Console\Command;

use Phpactor\Tests\Integration\SystemTestCase;

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

    /**
     * @testdox Replace method
     */
    public function testReplace()
    {
        $process = $this->phpactor('references:method "Animals\Badger" badge --replace=dodge');
        $this->assertSuccess($process);
        $this->assertContains('this->dodge()', file_get_contents(
            $this->workspaceDir() . '/lib/Badger.php'
        ));
    }

    /**
     * @testdox Replace dry run
     */
    public function testReplaceDryRun()
    {
        $process = $this->phpactor('references:method "Animals\Badger" badge --replace=dodge --dry-run');
        $this->assertSuccess($process);
        $this->assertContains('this->badge()', file_get_contents(
            $this->workspaceDir() . '/lib/Badger.php'
        ));
    }

    /**
     * @testdox It can use a different scope
     */
    public function testReferencesScope()
    {
        $process = $this->phpactor('references:method "Animals\Badger" badge --filesystem=composer');
        $this->assertSuccess($process);
        $this->assertContains('⟶badge⟵', $process->getOutput());
    }
}

