<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Application\ClassCopy;
use Phpactor\Rpc\Editor\InputCallbackAction;
use Phpactor\Rpc\Handler\ClassCopyHandler;
use Phpactor\Rpc\ActionRequest;
use Prophecy\Argument;
use Phpactor\Rpc\Editor\OpenFileAction;
use Phpactor\Rpc\Editor\Input\TextInput;
use Phpactor\Application\Logger\NullLogger;

class CopyFileHandlerTest extends HandlerTestCase
{
    const SOURCE_PATH = 'souce_path';
    const DEST_PATH = 'souce_path';

    /**
     * @var ClassCopy
     */
    private $classCopy;

    public function setUp()
    {
        $this->classCopy = $this->prophesize(ClassCopy::class);
    }

    public function createHandler(): Handler
    {
        return new ClassCopyHandler(
            $this->classCopy->reveal()
        );
    }


    /**
     * @testdox It should request the dest path if none is given.
     */
    public function testNoDestPath()
    {
        /** @var $action InputCallbackAction */
        $action = $this->handle('copy_class', [
            'source_path' => self::SOURCE_PATH,
            'dest_path' => null,
        ]);

        $this->assertInstanceOf(InputCallbackAction::class, $action);
        $inputs = $action->inputs();
        $this->assertCount(1, $inputs);
        $this->assertInstanceOf(TextInput::class, reset($inputs));
        $this->assertInstanceOf(ActionRequest::class, $action->callbackAction());
        $this->assertEquals('copy_class', $action->callbackAction()->name());
        $this->assertEquals([
            'source_path' => self::SOURCE_PATH,
            'dest_path' => null,
        ], $action->callbackAction()->parameters());
    }

    public function testCopyClass()
    {
        $this->classCopy->copy(
            Argument::type(NullLogger::class),
            self::SOURCE_PATH,
            self::DEST_PATH
        )->shouldBeCalled();

        /** @var $action InputCallbackAction */
        $action = $this->handle('copy_class', [
            'source_path' => self::SOURCE_PATH,
            'dest_path' => self::DEST_PATH,
        ]);

        $this->assertInstanceOf(OpenFileAction::class, $action);
    }
}
