<?php

namespace Phpactor\Tests\System\Extension\Core\Command;

use Phpactor\Tests\System\SystemTestCase;

class ConfigSetCommandTest extends SystemTestCase
{
    public function testStatus(): void
    {
        $process = $this->phpactorFromStringArgs('config:set foo true');
        $this->assertSuccess($process);
    }
}
