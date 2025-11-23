<?php

namespace Phpactor\Tests\Unit\Extension\Core\Console\Prompt;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Core\Console\Prompt\Prompt;
use Phpactor\Extension\Core\Console\Prompt\ChainPrompt;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use RuntimeException;

class ChainPromptTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<Prompt>
     */
    private ObjectProphecy $prompt1;

    /**
     * @var ObjectProphecy<Prompt>
     */
    private ObjectProphecy $prompt2;

    private ChainPrompt $chainPrompt;

    public function setUp(): void
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

    #[TestDox('It delegates to a supporting prompt')]
    public function testDelegateToSupporting(): void
    {
        $this->prompt1->isSupported()->willReturn(false);
        $this->prompt2->isSupported()->willReturn(true);

        $this->prompt2->prompt('Hello', 'World')->willReturn('Goodbye');

        $response = $this->chainPrompt->prompt('Hello', 'World');
        $this->assertEquals('Goodbye', $response);
    }

    #[TestDox('It throws an exception if no prompts are supported.')]
    public function testPromptsNotSupported(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not prompt');
        $this->prompt1->isSupported()->willReturn(false);
        $this->prompt2->isSupported()->willReturn(false);

        $this->chainPrompt->prompt('Hello', 'World');
    }
}
