<?php

namespace Phpactor\Tests\System\Console\Command;

use Phpactor\Tests\System\SystemTestCase;

class MoveCommandTest extends SystemTestCase
{
    public function setUp()
    {
        $this->initWorkspace();
        $this->loadProject('Animals');
    }

    /**
     * @testdox It moves class files.
     */
    public function testMoveClassFile1()
    {
        $process = $this->phpactor('mv lib/Badger/Carnivorous.php lib/Aardvark/Insectarian.php');
        $this->assertSuccess($process);
    }

    /**
     * @testdox It moves class files.
     */
    public function testMoveClassFile2()
    {
        $process = $this->phpactor('mv lib/Aardvark/Edentate.php lib/Foobar.php');
        $this->assertSuccess($process);
    }

    /**
     * @testdox It moves folders
     */
    public function testMoveClassFolder()
    {
        $process = $this->phpactor('mv lib/Aardvark lib/Elephant');
        $this->assertSuccess($process);
    }
}
