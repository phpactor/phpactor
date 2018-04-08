<?php

namespace Phpactor\Tests\Unit\Extension\ClassMover\Rpc;

use Phpactor\Rpc\Handler;
use Phpactor\Extension\ClassMover\Application\ClassMover;
use Phpactor\Rpc\Response\InputCallbackResponse;
use Phpactor\Rpc\Request;
use Phpactor\Extension\ClassMover\Application\ClassMoverLogger;
use Prophecy\Argument;
use Phpactor\Rpc\Response\OpenFileResponse;
use Phpactor\Rpc\Response\Input\TextInput;
use Phpactor\Extension\ClassMover\Rpc\ClassMoveHandler;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Rpc\Response\EchoResponse;
use Phpactor\Rpc\Response\CollectionResponse;
use Phpactor\Rpc\Response\CloseFileResponse;
use Phpactor\Rpc\Response\Input\ConfirmInput;
use Phpactor\Tests\Unit\Rpc\Handler\HandlerTestCase;

class ClassMoveHandlerTest extends HandlerTestCase
{
    const SOURCE_PATH = 'souce_path';
    const DEST_PATH = 'dest_path';

    /**
     * @var ClassMover
     */
    private $classMover;

    public function setUp()
    {
        $this->classMover = $this->prophesize(ClassMover::class);
    }

    public function createHandler(): Handler
    {
        return new ClassMoveHandler(
            $this->classMover->reveal(),
            SourceCodeFilesystemExtension::FILESYSTEM_GIT
        );
    }

    public function testNotConfirmed()
    {
        /** @var $action InputCallbackAction */
        $action = $this->handle('move_class', [
            'source_path' => self::SOURCE_PATH,
            'dest_path' => null,
            'confirmed' => false,
        ]);

        $this->assertInstanceOf(EchoResponse::class, $action);
        $this->assertContains('Cancelled', $action->message());
    }

    public function testConfirmChallenge()
    {
        /** @var $action InputCallbackAction */
        $action = $this->handle('move_class', [
            'source_path' => self::SOURCE_PATH,
            'dest_path' => self::DEST_PATH,
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $action);
        $inputs = $action->inputs();
        $this->assertCount(1, $inputs);
        $this->assertInstanceOf(ConfirmInput::class, reset($inputs));
        $this->assertInstanceOf(Request::class, $action->callbackAction());
        $this->assertEquals('move_class', $action->callbackAction()->name());
    }


    /**
     * @testdox It should request the dest path if none is given.
     */
    public function testNoDestPath()
    {
        /** @var $action InputCallbackAction */
        $action = $this->handle('move_class', [
            'source_path' => self::SOURCE_PATH,
            'dest_path' => null,
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $action);
        $inputs = $action->inputs();
        $this->assertCount(1, $inputs);
        $this->assertInstanceOf(TextInput::class, reset($inputs));
        $this->assertInstanceOf(Request::class, $action->callbackAction());
        $this->assertEquals('move_class', $action->callbackAction()->name());
        $this->assertEquals([
            'source_path' => self::SOURCE_PATH,
            'dest_path' => null,
            'confirmed' => null,
        ], $action->callbackAction()->parameters());
    }

    /**
     * @testdox It should ask for confirmation
     */
    public function testMoveClass()
    {
        /** @var $action StackAction */
        $action = $this->handle('move_class', [
            'source_path' => self::SOURCE_PATH,
            'dest_path' => self::DEST_PATH,
            'confirmed' => true,
        ]);

        $this->classMover->move(
            Argument::type(ClassMoverLogger::class),
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            self::SOURCE_PATH,
            self::DEST_PATH
        )->shouldBeCalled();

        $this->assertInstanceOf(CollectionResponse::class, $action);
        $actions = $action->actions();

        $firstAction = array_shift($actions);
        $this->assertInstanceOf(CloseFileResponse::class, $firstAction);
        $this->assertEquals(self::SOURCE_PATH, $firstAction->path());

        $secondAction = array_shift($actions);
        $this->assertInstanceOf(OpenFileResponse::class, $secondAction);
        $this->assertEquals(self::DEST_PATH, $secondAction->path());
    }
}
