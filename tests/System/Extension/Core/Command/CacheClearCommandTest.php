<?php

namespace Phpactor\Tests\System\Extension\Core\Command;

use Phpactor\Tests\System\SystemTestCase;

class CacheClearCommandTest extends SystemTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->loadProject('Animals');
    }

    public function testCacheClear()
    {
        $process = $this->phpactor('cache:clear');
        $this->assertSuccess($process);
        $this->assertStringContainsString('Cache cleared', $process->getOutput());
    }
}
