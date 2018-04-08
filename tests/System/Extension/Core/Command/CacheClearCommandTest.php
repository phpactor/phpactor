<?php

namespace Phpactor\Tests\System\Extension\Core\Command;

use Phpactor\Tests\System\SystemTestCase;

class CacheClearCommandTest extends SystemTestCase
{
    public function setUp()
    {
        $this->workspace()->reset();
        $this->loadProject('Animals');
    }

    public function testCacheClear()
    {
        $process = $this->phpactor('cache:clear');
        $this->assertSuccess($process);
        $this->assertContains('Cache cleared', $process->getOutput());
    }
}
