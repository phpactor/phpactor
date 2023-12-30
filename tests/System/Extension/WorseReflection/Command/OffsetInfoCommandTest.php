<?php

namespace Phpactor\Tests\System\Extension\WorseReflection\Command;

use Phpactor\Tests\System\SystemTestCase;

class OffsetInfoCommandTest extends SystemTestCase
{
    public function setUp(): void
    {
        $this->workspace()->reset();
        $this->loadProject('Animals');
    }

    /**
     * @testdox It provides information about the thing under the cursor.
     */
    public function testProvideInformationForOffset(): void
    {
        $process = $this->phpactorFromStringArgs('offset:info lib/Badger.php 163');
        $this->assertSuccess($process);
        $this->assertStringContainsString('type:Animals\Badger\Carnivorous', $process->getOutput());
        $this->assertStringContainsString('Badger/Carnivorous.php', $process->getOutput());
    }

    /**
     * @testdox It provides information about the thing under the cursor as JSON
     */
    public function testProvideInformationForOffsetAsJson(): void
    {
        $process = $this->phpactorFromStringArgs('offset:info lib/Badger.php 137 --format=json');
        $this->assertSuccess($process);
        $this->assertStringContainsString('{"symbol":"__construct', $process->getOutput());
    }
}
