<?php

namespace Phpactor\Tests\UserInterface\Console\Command;

use Phpactor\Tests\UserInterface\SystemTestCase;

class ClassSearchCommandTest extends SystemTestCase
{
    public function setUp()
    {
        $this->initWorkspace();
        $this->loadProject('Animals');
    }

    /**
     * @testdox It should return information baesd on a class "short" name.
     */
    public function testSearchName()
    {
        $process = $this->phpactor('class:named "Badger"');
        $this->assertSuccess($process);
    }
}

