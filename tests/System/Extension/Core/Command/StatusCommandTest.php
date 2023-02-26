<?php

namespace Phpactor\Tests\System\Extension\Core\Command;

use Phpactor\Tests\System\SystemTestCase;

class StatusCommandTest extends SystemTestCase
{
    public function testStatus(): void
    {
        $process = $this->phpactorFromStringArgs('status');
        $this->assertSuccess($process);
    }
}
