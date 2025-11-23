<?php

namespace Phpactor\Tests\System\Extension\ClassMover\Command;

use PHPUnit\Framework\Attributes\TestDox;
use Phpactor\Tests\System\SystemTestCase;

class ReferencesClassCommandTest extends SystemTestCase
{
    public function setUp(): void
    {
        $this->workspace()->reset();
        $this->loadProject('Animals');
    }

    #[TestDox('It should show all references to Badger')]
    public function testReferences(): void
    {
        $process = $this->phpactorFromStringArgs('references:class "Animals\Badger"');
        $this->assertSuccess($process);
        $this->assertStringContainsString('class ⟶Badger⟵', $process->getOutput());
    }

    #[TestDox('It should accept a format')]
    public function testReferencesFormatted(): void
    {
        $process = $this->phpactorFromStringArgs('references:class "Animals\Badger" --format=json');
        $this->assertSuccess($process);
        $this->assertStringContainsString('"line":"class Badger', $process->getOutput());
    }

    #[TestDox('It should replace class references')]
    public function testReferencesReplace(): void
    {
        $process = $this->phpactorFromStringArgs('references:class "Animals\Badger" --replace="Kangaroo"');
        $this->assertSuccess($process);
        $this->assertStringContainsString('class ⟶Kangaroo⟵', $process->getOutput());
        $this->assertStringContainsString('class Kangaroo', file_get_contents(
            $this->workspaceDir() . '/lib/Badger.php'
        ));
    }

    #[TestDox('It should replace class references')]
    public function testReferencesReplaceDryRun(): void
    {
        $process = $this->phpactorFromStringArgs('references:class "Animals\Badger" --dry-run --replace="Kangaroo"');
        $this->assertSuccess($process);
        $this->assertStringContainsString('class ⟶Kangaroo⟵', $process->getOutput());
        $this->assertStringNotContainsString('class Kangaroo', file_get_contents(
            $this->workspaceDir() . '/lib/Badger.php'
        ));
    }

    #[TestDox('It can use a different scope')]
    public function testReferencesScope(): void
    {
        $process = $this->phpactorFromStringArgs('references:class "Animals\Badger" --filesystem=simple');
        $this->assertSuccess($process);
        $this->assertStringContainsString('class ⟶Badger⟵', $process->getOutput());
    }
}
