<?php

namespace Phpactor\Tests\Unit\Extension\CoreTransform\Rpc;

use Phpactor\CodeTransform\Domain\Refactor\ExtractExpression;
use Phpactor\Extension\CodeTransformExtra\Rpc\ExtractExpressionHandler;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\Tests\Unit\Extension\Rpc\HandlerTestCase;

class ExtractExpressionHandlerTest extends HandlerTestCase
{
    const SOURCE = '<?php echo "foo";';
    const PATH = '/path/to';
    const OFFSET_START = 1234;
    const OFFSET_END = 1234;
    const VARIABLE_NAME = 'FOOBAR';

    /**
     * @var ExtractExpression
     */
    private $extractExpression;

    public function setUp()
    {
        $this->extractExpression = $this->prophesize(ExtractExpression::class);
    }

    public function createHandler(): Handler
    {
        return new ExtractExpressionHandler($this->extractExpression->reveal());
    }

    public function testDemandMethodName()
    {
        $action = $this->handle('extract_expression', [
            'source' => self::SOURCE,
            'path' => self::PATH,
            'offset_start' => self::OFFSET_START,
            'offset_end' => self::OFFSET_END,
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $action);
        $inputs = $action->inputs();
        $this->assertCount(1, $inputs);
        $firstInput = reset($inputs);
        $this->assertEquals(ExtractExpressionHandler::NAME, $action->callbackAction()->name());

        $this->assertInstanceOf(TextInput::class, $firstInput);
        $this->assertEquals('variable_name', $firstInput->name());
    }

    public function testExtractExpression()
    {
        $this->extractExpression->extractExpression(
            self::SOURCE,
            self::OFFSET_START,
            self::OFFSET_END,
            self::VARIABLE_NAME
        )->willReturn(SourceCode::fromStringAndPath('asd', '/path'));

        $action = $this->handle('extract_expression', [
            'source' => self::SOURCE,
            'path' => self::PATH,
            'offset_start' => self::OFFSET_START,
            'offset_end' => self::OFFSET_END,
            'variable_name' => self::VARIABLE_NAME,
        ]);

        $this->assertInstanceof(UpdateFileSourceResponse::class, $action);
    }
}
