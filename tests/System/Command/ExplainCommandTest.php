<?php

namespace Phpactor\Tests\System\Command;

use Phpactor\Tests\System\SystemTestCase;

class ExplainCommandTest extends SystemTestCase
{
    public function testComplete()
    {
        $process = $this->exec('explain ' . __DIR__ . '/../../Functional/Example/ClassOne.php');
        $this->assertSuccess($process);
    }
}

