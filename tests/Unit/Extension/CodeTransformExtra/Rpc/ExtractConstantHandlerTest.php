<?php

namespace Phpactor\Tests\Unit\Extension\CodeTransformExtra\Rpc;

use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;
use Phpactor\CodeTransform\Domain\Refactor\ExtractConstant;
use Phpactor\Extension\CodeTransformExtra\Rpc\ExtractConstantHandler;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\Tests\Unit\Extension\Rpc\HandlerTestCase;
use Phpactor\TextDocument\TextDocumentEdits;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Prophecy\Prophecy\ObjectProphecy;

class ExtractConstantHandlerTest extends HandlerTestCase
{
    const SOURCE = '<?php echo "foo";';
    const PATH = '/path/to';
    const OFFSET = 1234;
    const CONSTANT_NAME = 'FOOBAR';

    private ObjectProphecy $extractConstant;

    public function setUp(): void
    {
        $this->extractConstant = $this->prophesize(ExtractConstant::class);
    }

    public function createHandler(): Handler
    {
        return new ExtractConstantHandler($this->extractConstant->reveal());
    }

    public function testDemandConstantName(): void
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

    public function testExtractConstant(): void
    {
        $this->extractConstant->extractConstant(
            self::SOURCE,
            self::OFFSET,
            self::CONSTANT_NAME
        )->willReturn(new TextDocumentEdits(
            TextDocumentUri::fromString('file://'. self::PATH),
            TextEdits::one(TextEdit::create(6, 10, 'newMethod()'))
        ));

        $action = $this->handle('extract_constant', [
            'source' => self::SOURCE,
            'path' => self::PATH,
            'offset' => self::OFFSET,
            'constant_name' => self::CONSTANT_NAME,
        ]);

        $this->assertInstanceof(UpdateFileSourceResponse::class, $action);
    }
}
