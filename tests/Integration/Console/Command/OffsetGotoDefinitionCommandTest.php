<?php

namespace Phpactor\Tests\Integration\Console\Command;

use Phpactor\Tests\Integration\SystemTestCase;

class OffsetDefinitionCommandTest extends SystemTestCase
{
    public function setUp()
    {
        $this->initWorkspace();
        $this->loadProject('Animals');
    }

    /**
     * @testdox It provides information about the thing under the cursor.
     */
    public function testProvideInformationForOffset()
    {
        $process = $this->phpactor('offset:definition lib/Badger.php 182');
        $this->assertSuccess($process);
        $this->assertContains('offset:87', $process->getOutput());
        $this->assertContains('lib/Badger.php', $process->getOutput());
    }
}
