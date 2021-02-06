<?php

namespace Phpactor\Tests\System\Extension\Core\Command;

use Phpactor\Tests\System\SystemTestCase;

class StatusCommandTest extends SystemTestCase
{
    public function testStatus(): void
    {
        $process = $this->phpactor('status');
        $this->assertSuccess($process);
    }
}
