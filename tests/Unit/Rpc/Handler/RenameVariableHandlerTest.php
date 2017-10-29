<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use Phpactor\Rpc\Handler\RenameVariableHandler;
use Phpactor\CodeTransform\Domain\Refactor\RenameVariable;
use Phpactor\Rpc\Handler;
use Phpactor\Rpc\Response\InputCallbackResponse;
use Phpactor\Rpc\Response\Input\TextInput;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Rpc\Response\ReplaceFileSourceResponse;
use Phpactor\Rpc\Response\Input\ChoiceInput;

class RenameVariableHandlerTest extends HandlerTestCase
{
    const SOURCE = '<?php echo "foo";';
    const PATH = '/path/to';
    const OFFSET = 1234;
    const VARIABLE_NAME = 'FOOBAR';

    /**
     * @var RenameVariable
     */
    private $renameVariable;

    public function setUp()
    {
        $this->renameVariable = $this->prophesize(RenameVariable::class);
    }

    public function createHandler(): Handler
    {
        return new RenameVariableHandler($this->renameVariable->reveal());
    }

    public function testDemandVariableName()
    {
        $action = $this->handle(RenameVariableHandler::NAME, [
            RenameVariableHandler::PARAM_SOURCE => self::SOURCE,
            RenameVariableHandler::PARAM_PATH => self::PATH,
            RenameVariableHandler::PARAM_OFFSET => self::OFFSET,
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $action);
        $inputs = $action->inputs();
        $this->assertCount(2, $inputs);
        $this->assertEquals(RenameVariableHandler::NAME, $action->callbackAction()->name());

        array_shift($inputs);
        $firstInput = array_shift($inputs);
        $this->assertInstanceOf(TextInput::class, $firstInput);
        $this->assertEquals('name', $firstInput->name());
    }

    public function testDemandScope()
    {
        $action = $this->handle(RenameVariableHandler::NAME, [
            RenameVariableHandler::PARAM_SOURCE => self::SOURCE,
            RenameVariableHandler::PARAM_PATH => self::PATH,
            RenameVariableHandler::PARAM_OFFSET => self::OFFSET,
            RenameVariableHandler::PARAM_NAME => self::VARIABLE_NAME,
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $action);
        $inputs = $action->inputs();
        $this->assertCount(1, $inputs);
        $firstInput = reset($inputs);
        $this->assertEquals(RenameVariableHandler::NAME, $action->callbackAction()->name());

        $this->assertInstanceOf(ChoiceInput::class, $firstInput);
        $this->assertEquals('scope', $firstInput->name());
    }

    public function testRenameVariable()
    {
        $this->renameVariable->renameVariable(
            self::SOURCE,
            self::OFFSET,
            self::VARIABLE_NAME,
            RenameVariable::SCOPE_FILE
        )->willReturn(SourceCode::fromStringAndPath('asd', '/path'));

        $action = $this->handle(RenameVariableHandler::NAME, [
            RenameVariableHandler::PARAM_SOURCE => self::SOURCE,
            RenameVariableHandler::PARAM_PATH => self::PATH,
            RenameVariableHandler::PARAM_OFFSET => self::OFFSET,
            RenameVariableHandler::PARAM_NAME => self::VARIABLE_NAME,
            RenameVariableHandler::PARAM_SCOPE => RenameVariable::SCOPE_FILE
        ]);

        $this->assertInstanceof(ReplaceFileSourceResponse::class, $action);
    }
}
