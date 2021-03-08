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
     * @testdox It should return information based on a class "short" name.
     */
    public function testSearchName(): void
    {
        $process = $this->phpactor('class:search "Badger"');
        $this->assertSuccess($process);
        $this->assertStringContainsString('Badger.php', $process->getOutput());
    }

    /**
     * @testdox It should return JSON information based on a class "short" name.
     */
    public function testSearchNameJson(): void
    {
        $process = $this->phpactor('class:search "Badger" --format=json');
        $this->assertSuccess($process);
        $this->assertStringContainsString('Badger.php"', $process->getOutput());
    }

    /**
     * @testdox It should not return non-PHP files in results.
     */
    public function testSearchNameOnlyPhp()
    {
        $process = $this->phpactor('class:search "Badger"');
        $this->assertSuccess($process);
        $this->assertStringNotContainsString('Badger.php.html', $process->getOutput());
    }

    /**
     * @testdox It should return information based on a fully-qualified class name.
     */
    public function testSearchByQualifiedName(): void
    {
        $process = $this->phpactor('class:search "Badger\\Carnivorous" --format=json');
        $this->assertSuccess($process);
        $this->assertStringContainsString('Carnivorous.php"', $process->getOutput());
    }

    /**
     * @testdox It should return information based on an "internal" class name.
     */
    public function testSearchNameInternalName(): void
    {
        $process = $this->phpactor('class:search "DateTime" --format=json');
        $this->assertSuccess($process);
        $this->assertStringContainsString('DateTime', $process->getOutput());
    }
}
