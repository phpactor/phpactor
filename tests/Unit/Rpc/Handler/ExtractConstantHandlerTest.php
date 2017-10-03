<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Rpc\Editor\Input\ChoiceInput;
use Phpactor\Rpc\Editor\InputCallbackAction;
use Phpactor\CodeTransform\CodeTransform;
use Phpactor\Rpc\Handler\TransformHandler;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Rpc\Editor\ReplaceFileSourceAction;
use Phpactor\CodeTransform\Domain\Refactor\ExtractConstant;
use Phpactor\Rpc\Handler\ExtractConstantHandler;
use Phpactor\Rpc\Editor\Input\TextInput;

class ExtractConstantHandlerTest extends HandlerTestCase
{
    const SOURCE = '<?php echo "foo";';
    const PATH = '/path/to';
    const OFFSET = 1234;

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

        $this->assertInstanceOf(InputCallbackAction::class, $action);
        $inputs = $action->inputs();
        $this->assertCount(1, $inputs);
        $firstInput = reset($inputs);
        $this->assertEquals(ExtractConstantHandler::NAME, $action->callbackAction()->name());

        $this->assertInstanceOf(TextInput::class, $firstInput);
        $this->assertEquals('constant_name', $firstInput->name());
    }

    public function testExtractConstant()
    {
        $action = $this->handle('extract_constant', [
            'source' => self::SOURCE,
            'path' => self::PATH,
            'offset' => self::OFFSET,
            'constant_name' => 'FOOBAR',
        ]);

        $this->assertInstanceof(ReplaceFileSourceAction::class, $action);
    }
}
