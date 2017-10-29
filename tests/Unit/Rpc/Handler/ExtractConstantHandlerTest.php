<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Rpc\Response\InputCallbackResponse;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Rpc\Response\ReplaceFileSourceResponse;
use Phpactor\CodeTransform\Domain\Refactor\ExtractConstant;
use Phpactor\Rpc\Handler\ExtractConstantHandler;
use Phpactor\Rpc\Response\Input\TextInput;

class ExtractConstantHandlerTest extends HandlerTestCase
{
    const SOURCE = '<?php echo "foo";';
    const PATH = '/path/to';
    const OFFSET = 1234;
    const CONSTANT_NAME = 'FOOBAR';

    /**
     * @var ExtractConstant
     */
    private $extractConstant;

    public function setUp()
    {
        $this->extractConstant = $this->prophesize(ExtractConstant::class);
    }

    public function createHandler(): Handler
    {
        return new ExtractConstantHandler($this->extractConstant->reveal());
    }

    public function testDemandConstantName()
    {
        $action = $this->handle('extract_constant', [
            'source' => self::SOURCE,
            'path' => self::PATH,
            'offset' => self::OFFSET,
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $action);
        $inputs = $action->inputs();
        $this->assertCount(1, $inputs);
        $firstInput = reset($inputs);
        $this->assertEquals(ExtractConstantHandler::NAME, $action->callbackAction()->name());

        $this->assertInstanceOf(TextInput::class, $firstInput);
        $this->assertEquals('constant_name', $firstInput->name());
    }

    public function testExtractConstant()
    {
        $this->extractConstant->extractConstant(
            self::SOURCE,
            self::OFFSET,
            self::CONSTANT_NAME
        )->willReturn(SourceCode::fromStringAndPath('asd', '/path'));

        $action = $this->handle('extract_constant', [
            'source' => self::SOURCE,
            'path' => self::PATH,
            'offset' => self::OFFSET,
            'constant_name' => self::CONSTANT_NAME,
        ]);

        $this->assertInstanceof(ReplaceFileSourceResponse::class, $action);
    }
}
