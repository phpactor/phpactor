<?php

namespace Phpactor\Tests\System\Extension\ClassMover\Command;

use Phpactor\Tests\System\SystemTestCase;

class ReferencesMemberCommandTest extends SystemTestCase
{
    public function setUp(): void
    {
        $this->workspace()->reset();
        $this->loadProject('Animals');
    }

    /**
     * @testdox It should show all references to Badger
     */
    public function testReferences(): void
    {
        $process = $this->phpactorFromStringArgs('references:member "Animals\Badger" badge');
        $this->assertSuccess($process);
        $this->assertStringContainsString('$this->⟶badge⟵', $process->getOutput());
    }

    /**
     * @testdox When non-existing member given, suggest existing members with exception.
     */
    public function testNonExistingMember(): void
    {
        $process = $this->phpactorFromStringArgs('references:member "Animals\Badger" bad --type="method"');
        $this->assertEquals(255, $process->getExitCode());
        $this->assertStringContainsString('Class has no member named "bad"', $process->getErrorOutput());
    }

    /**
     * @testdox Find all members for class
     */
    public function testFindAllForClass(): void
    {
        $process = $this->phpactorFromStringArgs('references:member "Animals\Badger"');
        $this->assertSuccess($process);
    }

    /**
     * @testdox Find all members
     */
    public function testFindAll(): void
    {
        $process = $this->phpactorFromStringArgs('references:member');
        $this->assertSuccess($process);
    }

    /**
     * @testdox Replace member
     */
    public function testReplace(): void
    {
        $process = $this->phpactorFromStringArgs('references:member "Animals\Badger" badge --replace=dodge');
        $this->assertSuccess($process);
        $this->assertStringContainsString('this->dodge()', file_get_contents(
            $this->workspaceDir() . '/lib/Badger.php'
        ));
    }

    /**
     * @testdox Replace dry run
     */
    public function testReplaceDryRun(): void
    {
        $process = $this->phpactorFromStringArgs('references:member "Animals\Badger" badge --replace=dodge --dry-run');
        $this->assertSuccess($process);
        $this->assertStringContainsString('this->badge()', file_get_contents(
            $this->workspaceDir() . '/lib/Badger.php'
        ));
    }

    /**
     * @testdox It can use a different scope
     */
    public function testReferencesScope(): void
    {
        $process = $this->phpactorFromStringArgs('references:member "Animals\Badger" badge --filesystem=composer');
        $this->assertSuccess($process);
        $this->assertStringContainsString('⟶badge⟵', $process->getOutput());
    }

    /**
     * @testdox By property
     */
    public function testByTypeProperty(): void
    {
        $process = $this->phpactorFromStringArgs('references:member "Animals\Badger" carnivorous --type=property');
        $this->assertSuccess($process);
        $this->assertStringContainsString('⟶carnivorous⟵', $process->getOutput());
    }

    /**
     * @testdox Find member name shared by different types
     */
    public function testDifferentTypes(): void
    {
        $process = $this->phpactorFromStringArgs('references:member "Animals\Badger" carnivorous');
        $this->assertSuccess($process);
        $this->assertStringContainsString('$this->⟶carnivorous⟵ = $carnivorous', $process->getOutput());
        $this->assertStringContainsString('public function ⟶carnivorous⟵(', $process->getOutput());
    }
}
