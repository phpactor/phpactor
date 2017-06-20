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
        $this->assertEquals(<<<'EOT'
[MOVE] lib/Badger/Carnivorous.php => lib/Aardvark/Insectarian.php
[REPL] lib/Aardvark/Edentate.php: Animals\Badger\Carnivorous => Animals\Aardvark\Insectarian
[REPL] lib/Aardvark/Insectarian.php: Animals\Badger\Carnivorous => Animals\Aardvark\Insectarian
[REPL] lib/Badger.php: Animals\Badger\Carnivorous => Animals\Aardvark\Insectarian

EOT
        , $process->getOutput());
    }

    /**
     * @testdox It moves class files.
     */
    public function testMoveClassFile2()
    {
        $process = $this->phpactor('mv lib/Aardvark/Edentate.php lib/Foobar.php');
        $this->assertSuccess($process);
        $this->assertEquals(<<<'EOT'
[MOVE] lib/Badger/Carnivorous.php => lib/Aardvark/Insectarian.php
[REPL] lib/Aardvark/Edentate.php: Animals\Badger\Carnivorous => Animals\Aardvark\Insectarian
[REPL] lib/Aardvark/Insectarian.php: Animals\Badger\Carnivorous => Animals\Aardvark\Insectarian
[REPL] lib/Badger.php: Animals\Badger\Carnivorous => Animals\Aardvark\Insectarian

EOT
        , $process->getOutput());
    }
}
