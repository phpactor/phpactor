<?php

namespace Phpactor\Tests\System\Extension\Core\Command;

use Phpactor\Tests\System\SystemTestCase;

class ConfigDumpCommandTest extends SystemTestCase
{
    public function testConfigDump()
    {
        $process = $this->phpactor('config:dump');
        $this->assertSuccess($process);
        $this->assertContains('Config files', $process->getOutput());
    }

    /**
     * @testdox It should dump only configuration
     */
    public function testConfigDumpOnly()
    {
        $process = $this->phpactor('config:dump --config-only');
        $this->assertSuccess($process);
        $config = json_decode($process->getOutput(), true);
        $this->assertInternalType('array', $config);
    }
}
