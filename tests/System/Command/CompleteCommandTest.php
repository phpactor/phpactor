<?php

namespace Phpactor\Tests\System\Command;

use Phpactor\Tests\System\SystemTestCase;

class CompleteCommandTest extends SystemTestCase
{
    public function testComplete()
    {
        $process = $this->exec('complete --offset=256 ' . __DIR__ . '/../../Functional/Example/ClassOne.php');
        $this->assertSuccess($process);
        $output = json_decode($process->getOutput(), true);
        $this->assertInternalType('array', $output);
    }
}
