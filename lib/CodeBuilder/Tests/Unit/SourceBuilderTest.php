<?php

namespace Phpactor\CodeBuilder\Tests\Unit;

use Prophecy\PhpUnit\ProphecyTrait;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\SourceBuilder;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Domain\Prototype;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\TextDocument\TextEdits;
use Prophecy\Prophecy\ObjectProphecy;

class SourceBuilderTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<Updater>
     */
    private ObjectProphecy $updater;

    private $builder;

    private $generator;

    private $prototype;

    protected function setUp(): void
    {
        $this->generator = $this->prophesize(Renderer::class);
        $this->updater = $this->prophesize(Updater::class);
        $this->builder = new SourceBuilder(
            $this->generator->reveal(),
            $this->updater->reveal()
        );
        $this->prototype = $this->prophesize(Prototype\Prototype::class);
    }

    /**
     * @testdoc It should delegate to the generator.
     */
    public function testGenerate(): void
    {
        $expectedCode = Code::fromString('');
        $this->generator->render($this->prototype->reveal())->willReturn($expectedCode);
        $code = $this->builder->render($this->prototype->reveal());

        $this->assertSame($expectedCode, $code);
    }

    /**
     * @testdoc It should delegate to the updater.
     */
    public function testUpdate(): void
    {
        $sourceCode = Code::fromString('');
        $this->updater->textEditsFor($this->prototype->reveal(), $sourceCode)->willReturn(TextEdits::none());
        $code = $this->builder->apply($this->prototype->reveal(), $sourceCode);

        $this->assertEquals('', $code);
    }
}
