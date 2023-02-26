<?php

namespace Phpactor\Tests\System\Extension\Core\Command;

use Phpactor\Tests\System\SystemTestCase;

class ContainerDumpCommandTest extends SystemTestCase
{
    public function testConfigDump(): void
    {
        $process = $this->phpactorFromStringArgs('container:dump --services --tags --tag=worse_reflection.source_locator');
        $this->assertSuccess($process);
    }
}
