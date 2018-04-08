<?php

namespace Phpactor\Tests\Unit\Extension\Core\Console\Prompt;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Core\Console\Prompt\Prompt;
use Phpactor\Extension\Core\Console\Prompt\ChainPrompt;

class ChainPromptTest extends TestCase
{
    /**
     * @var Prompt
     */
    private $prompt1;

    /**
     * @var Prompt
     */
    private $prompt2;

    public function setUp()
    {
        $this->prompt1 = $this->prophesize(Prompt::class);
        $this->prompt1->name()->willReturn('prompt1');
        $this->prompt2 = $this->prophesize(Prompt::class);
        $this->prompt2->name()->willReturn('prompt2');
        $this->chainPrompt = new ChainPrompt([
            $this->prompt1->reveal(),
            $this->prompt2->reveal(),
        ]);
    }

    /**
     * @testdox It delegates to a supporting prompt
     */
    public function testDelegateToSupporting()
    {
        $this->prompt1->isSupported()->willReturn(false);
        $this->prompt2->isSupported()->willReturn(true);

        $this->prompt2->prompt('Hello', 'World')->willReturn('Goodbye');

        $response = $this->chainPrompt->prompt('Hello', 'World');
        $this->assertEquals('Goodbye', $response);
    }

    /**
     * @testdox It throws an exception if no prompts are supported.
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not prompt
     */
    public function testPromptsNotSupported()
    {
        $this->prompt1->isSupported()->willReturn(false);
        $this->prompt2->isSupported()->willReturn(false);

        $this->chainPrompt->prompt('Hello', 'World');
    }
}
