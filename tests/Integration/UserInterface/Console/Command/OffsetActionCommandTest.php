<?php

namespace Phpactor\Tests\Integration\Console\Command;

use Phpactor\Tests\Integration\SystemTestCase;

class OffsetActionCommandTest extends SystemTestCase
{
    public function setUp()
    {
        $this->initWorkspace();
        $this->loadProject('Animals');
    }

    /**
     * @testdox It returns possible actions for class and offset
     */
    public function testChoices()
    {
        $process = $this->phpactor('offset:action lib/Badger.php 270');
        $this->assertSuccess($process);

        $this->assertContains('goto_definition', $process->getOutput());
    }

    /**
     * @testdox It returns possible actions for class and offset as JSON
     */
    public function testChoicesAsJson()
    {
        $process = $this->phpactor('offset:action lib/Badger.php 270 --format=json');
        $this->assertSuccess($process);

        $choices = json_decode($process->getOutput(), true);
        $this->assertArrayHasKey('choices', $choices);
    }

    /**
     * @testdox It returns an action for the class and offset
     */
    public function testAction()
    {
        $process = $this->phpactor('offset:action lib/Badger.php 270 goto_definition');
        $this->assertSuccess($process);

        $this->assertContains('Badger.php', $process->getOutput());
    }
}
