<?php

namespace Phpactor\Tests\System\Extension\Core\Command;

use Phpactor\Tests\System\SystemTestCase;

class CacheClearCommandTest extends SystemTestCase
{
    public function setUp(): void
    {
        $this->workspace()->reset();
        $this->loadProject('Animals');
    }

    public function testCacheClear(): void
    {
        $process = $this->phpactor('cache:clear');
        $this->assertSuccess($process);
        $this->assertStringContainsString('Cache cleared', $process->getOutput());
    }
}
