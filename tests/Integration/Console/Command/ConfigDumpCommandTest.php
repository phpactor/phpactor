<?php

namespace Phpactor\Tests\Integration\Console\Command;

use Phpactor\Tests\Integration\SystemTestCase;

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
        $process = $this->phpactor('config:dump --config-only --format=json');
        $this->assertSuccess($process);
        $config = json_decode($process->getOutput(), true);
        $this->assertInternalType('array', $config);
    }
}

