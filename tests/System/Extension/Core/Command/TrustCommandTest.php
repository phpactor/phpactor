<?php

namespace Phpactor\Tests\System\Extension\Core\Command;

use Phpactor\Tests\System\SystemTestCase;

class TrustCommandTest extends SystemTestCase
{
    public function testTrust(): void
    {
        $process = $this->phpactorFromStringArgs('config:trust --trust');
        $this->assertSuccess($process);
    }
}
