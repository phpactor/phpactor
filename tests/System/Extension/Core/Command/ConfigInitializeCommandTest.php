<?php

namespace Phpactor\Tests\System\Extension\Core\Command;

use Phpactor\Tests\System\SystemTestCase;

class ConfigInitializeCommandTest extends SystemTestCase
{
    public function testStatus(): void
    {
        $process = $this->phpactor('config:initialize');
        $this->assertSuccess($process);
    }
}
