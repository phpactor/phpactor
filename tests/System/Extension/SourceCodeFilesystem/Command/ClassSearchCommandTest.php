<?php

namespace Phpactor\Tests\System\Extension\SourceCodeFilesystem\Command;

use Phpactor\Tests\System\SystemTestCase;

class ClassSearchCommandTest extends SystemTestCase
{
    public function setUp(): void
    {
        $this->workspace()->reset();
        $this->loadProject('Animals');
    }

    /**
     * @testdox It should return information baesd on a class "short" name.
     */
    public function testSearchName(): void
    {
        $process = $this->phpactorFromStringArgs('class:search "Badger"');
        $this->assertSuccess($process);
        $this->assertStringContainsString('Badger.php', $process->getOutput());
    }

    /**
     * @testdox It should return information baesd on a class "short" name.
     */
    public function testSearchNameJson(): void
    {
        $process = $this->phpactorFromStringArgs('class:search "Badger" --format=json');
        $this->assertSuccess($process);
        $this->assertStringContainsString('Badger.php"', $process->getOutput());
    }

    public function testSearchByQualifiedName(): void
    {
        $process = $this->phpactorFromStringArgs('class:search "Badger\\Carnivorous" --format=json');
        $this->assertSuccess($process);
        $this->assertStringContainsString('Carnivorous.php"', $process->getOutput());
    }

    /**
     * @testdox It should return information baesd on a class "short" name.
     */
    public function testSearchNameInternalName(): void
    {
        $process = $this->phpactorFromStringArgs('class:search "DateTime" --format=json');
        $this->assertSuccess($process);
        $this->assertStringContainsString('DateTime', $process->getOutput());
    }
}
