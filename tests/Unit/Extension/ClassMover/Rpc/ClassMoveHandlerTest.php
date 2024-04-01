<?php

namespace Phpactor\Tests\Unit\Extension\ClassMover\Rpc;

use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\ClassMover\Application\ClassMover;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\ClassMover\Application\Logger\ClassMoverLogger;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Prophecy\Argument;
use Phpactor\Extension\Rpc\Response\OpenFileResponse;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\Extension\ClassMover\Rpc\ClassMoveHandler;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\Response\CollectionResponse;
use Phpactor\Extension\Rpc\Response\CloseFileResponse;
use Phpactor\Extension\Rpc\Response\Input\ConfirmInput;
use Phpactor\Tests\Unit\Extension\Rpc\HandlerTestCase;
use Prophecy\Prophecy\ObjectProphecy;

class ClassMoveHandlerTest extends HandlerTestCase
{
    const SOURCE_PATH = 'souce_path';
    const DEST_PATH = 'dest_path';

    private ObjectProphecy $classMover;

    public function setUp(): void
    {
        $this->classMover = $this->prophesize(ClassMover::class);
        $this->classMover->getRelatedFiles(self::SOURCE_PATH)->willReturn([]);
    }

    public function createHandler(): Handler
    {
        return new ClassMoveHandler(
            $this->classMover->reveal(),
            SourceCodeFilesystemExtension::FILESYSTEM_GIT
        );
    }

    public function testNotConfirmed(): void
    {
        /** @var InputCallbackAction $action */
        $action = $this->handle('move_class', [
            'source_path' => self::SOURCE_PATH,
            'dest_path' => null,
            'confirmed' => false,
        ]);

        $this->assertInstanceOf(EchoResponse::class, $action);
        $this->assertStringContainsString('Cancelled', $action->message());
    }

    public function testConfirmChallenge(): void
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
    public function testNoDestPath(): void
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
            'move_related' => null
        ], $action->callbackAction()->parameters());
    }

    public function testItShouldAskForConfirmation(): void
    {
        $this->classMover->move(
            Argument::type(ClassMoverLogger::class),
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            self::SOURCE_PATH,
            self::DEST_PATH,
            false
        )->shouldBeCalled();

        /** @var $action StackAction */
        $action = $this->handle('move_class', [
            'source_path' => self::SOURCE_PATH,
            'dest_path' => self::DEST_PATH,
            'confirmed' => true,
        ]);

        $this->assertInstanceOf(CollectionResponse::class, $action);
        $actions = $action->actions();

        $action = array_shift($actions);
        $this->assertInstanceOf(OpenFileResponse::class, $action);
        $this->assertEquals(self::DEST_PATH, $action->path());

        $action = array_shift($actions);
        $this->assertInstanceOf(CloseFileResponse::class, $action);
        $this->assertEquals(self::SOURCE_PATH, $action->path());
    }

    public function testItAskIfRelatedFilesShouldBeMoved(): void
    {
        $this->classMover->getRelatedFiles(self::SOURCE_PATH)->willReturn([
            'foobar.php',
        ]);

        /** @var $action StackAction */
        $action = $this->handle('move_class', [
            'source_path' => self::SOURCE_PATH,
            'dest_path' => self::DEST_PATH,
            'confirmed' => true,
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $action);
        $inputs = $action->inputs();
        $input = reset($inputs);
        $this->assertInstanceOf(ConfirmInput::class, $input);
        $this->assertEquals('move_related', $input->name());
    }

    public function testMovesRelatedFiles(): void
    {
        $this->classMover->move(
            Argument::type(ClassMoverLogger::class),
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            self::SOURCE_PATH,
            self::DEST_PATH,
            true
        )->shouldBeCalled();

        $this->classMover->getRelatedFiles(self::SOURCE_PATH)->willReturn([
            'foobar.php',
        ]);

        /** @var $action StackAction */
        $action = $this->handle('move_class', [
            'source_path' => self::SOURCE_PATH,
            'dest_path' => self::DEST_PATH,
            'confirmed' => true,
            'move_related' => true,
        ]);
    }
}
