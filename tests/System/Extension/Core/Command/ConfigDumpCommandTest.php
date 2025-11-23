<?php

namespace Phpactor\Tests\System\Extension\Core\Command;

use PHPUnit\Framework\Attributes\TestDox;
use Phpactor\Tests\System\SystemTestCase;

class ConfigDumpCommandTest extends SystemTestCase
{
    public function testConfigDump(): void
    {
        $process = $this->phpactorFromStringArgs('config:dump');
        $this->assertSuccess($process);
        $this->assertStringContainsString('Config files', $process->getOutput());
    }

    #[TestDox('It should dump only configuration')]
    public function testConfigDumpOnly(): void
    {
        $process = $this->phpactorFromStringArgs('config:dump --config-only');
        $this->assertSuccess($process);
        $config = json_decode($process->getOutput(), true);
        $this->assertIsArray($config);
    }
}
