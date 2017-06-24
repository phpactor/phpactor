<?php

namespace Phpactor\Tests\UserInterface\Console\Command;

use Phpactor\Tests\UserInterface\SystemTestCase;

class OffsetInformationCommandTest extends SystemTestCase
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
        $process = $this->phpactor('offset:info lib/Badger.php 137');
        $this->assertSuccess($process);
        var_dump($process->getOutput());die();;
    }

}
