<?php

namespace Phpactor\Tests\Unit\Extension\CodeTransformExtra\Rpc;

use Phpactor\CodeTransform\Domain\Refactor\ExtractExpression;
use Phpactor\Extension\CodeTransformExtra\Rpc\ExtractExpressionHandler;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\Tests\Unit\Extension\Rpc\HandlerTestCase;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Prophecy\Prophecy\ObjectProphecy;

class ExtractExpressionHandlerTest extends HandlerTestCase
{
    const SOURCE = '<?php "foo";';
    const PATH = '/path/to';
    const OFFSET_START = 1234;
    const OFFSET_END = 1234;
    const VARIABLE_NAME = 'FOOBAR';

    /**
     * @var ObjectProphecy<ExtractExpression>
     */
    private ObjectProphecy $extractExpression;

    public function setUp(): void
    {
        $this->extractExpression = $this->prophesize(ExtractExpression::class);
    }

    public function createHandler(): Handler
    {
        return new ExtractExpressionHandler($this->extractExpression->reveal());
    }

    public function testDemandMethodName(): void
    {
        $action = $this->handle('extract_expression', [
            'source' => self::SOURCE,
            'path' => self::PATH,
            'offset_start' => self::OFFSET_START,
            'offset_end' => self::OFFSET_END,
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $action);
        assert($action instanceof InputCallbackResponse);
        $inputs = $action->inputs();
        $this->assertCount(1, $inputs);
        $firstInput = reset($inputs);
        $this->assertEquals(ExtractExpressionHandler::NAME, $action->callbackAction()->name());

        $this->assertInstanceOf(TextInput::class, $firstInput);
        $this->assertEquals('variable_name', $firstInput->name());
    }

    public function testExtractExpression(): void
    {
        $this->extractExpression->extractExpression(
            self::SOURCE,
            self::OFFSET_START,
            self::OFFSET_END,
            self::VARIABLE_NAME
        )
        ->shouldBeCalled()
        ->willReturn(TextEdits::one(TextEdit::create(6, 5, '$newVar = "foo"')));

        $action = $this->handle('extract_expression', [
            'source' => self::SOURCE,
            'path' => self::PATH,
            'offset_start' => self::OFFSET_START,
            'offset_end' => self::OFFSET_END,
            'variable_name' => self::VARIABLE_NAME,
        ]);

        $this->assertInstanceOf(UpdateFileSourceResponse::class, $action);
        assert($action instanceof UpdateFileSourceResponse);
        self::assertEquals('<?php $newVar = "foo";', $action->newSource());
    }
}
