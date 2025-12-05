<?php

namespace Phpactor\Extension\CodeTransform\Tests\Unit\Rpc;

use PHPUnit\Framework\TestCase;
use Phpactor\ClassFileConverter\Domain\FileToClass;
use Phpactor\Extension\CodeTransform\Rpc\ClassInflectHandler;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\Extension\Rpc\Response\Input\ChoiceInput;
use Phpactor\Extension\Rpc\Response\Input\ConfirmInput;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\Extension\Rpc\Test\HandlerTester;
use Phpactor\TestUtils\Workspace;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Filesystem\Path;

abstract class AbstractClassGenerateHandlerTest extends TestCase
{
    use ProphecyTrait;
    
    const EXAMPLE_PATH = '/path/to.php';
    const EXAMPLE_NEW_PATH = '/new/path.php';
    const EXAMPLE_VARIANT = 'one';
    const EXAMPLE_CLASS_1 = 'exampleClassName1';
    const EXAMPLE_CLASS_2 = 'exampleClassName2';

    protected ObjectProphecy $fileToClass;

    protected Workspace $workspace;

    public function setUp(): void
    {
        $this->fileToClass = $this->prophesize(FileToClass::class);
        $this->workspace = Workspace::create(__DIR__ . '/../../Workspace');
        $this->workspace->reset();
    }

    public function testAsksToOverwriteExistingFile(): void
    {
        $path = $this->workspace->path('foo');
        file_put_contents($path, 'foo');

        $response = $this->createTester()->handle($this->createHandler()->name(), [
            ClassInflectHandler::PARAM_CURRENT_PATH => self::EXAMPLE_PATH,
            ClassInflectHandler::PARAM_NEW_PATH => $path,
            ClassInflectHandler::PARAM_VARIANT=> self::EXAMPLE_VARIANT,
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $response);
        $input = $response->inputs()[0];
        $this->assertInstanceOf(ConfirmInput::class, $input);
        $this->assertEquals(ClassInflectHandler::PARAM_OVERWRITE_EXISTING, $input->name());
    }

    public function testCancelsOverwritesExistingFile(): void
    {
        $path = $this->workspace->path('foo');
        file_put_contents($path, 'foo');

        $response = $this->createTester()->handle($this->createHandler()->name(), [
            ClassInflectHandler::PARAM_CURRENT_PATH => $path,
            ClassInflectHandler::PARAM_NEW_PATH => $path,
            ClassInflectHandler::PARAM_VARIANT=> self::EXAMPLE_VARIANT,
            ClassInflectHandler::PARAM_OVERWRITE_EXISTING => false,
        ]);

        $this->assertInstanceOf(EchoResponse::class, $response);
    }

    public function testAsksForVariant(): void
    {
        $response = $this->createTester()->handle($this->createHandler()->name(), [
            ClassInflectHandler::PARAM_CURRENT_PATH => self::EXAMPLE_PATH
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $response);
        $this->assertCount(2, $response->inputs());
        $input = $response->inputs()[0];
        $this->assertInstanceOf(ChoiceInput::class, $input);

        $input = $response->inputs()[1];
        $this->assertInstanceOf(TextInput::class, $input);
    }

    abstract public function createHandler(): Handler;

    protected function createTester(): HandlerTester
    {
        return new HandlerTester($this->createHandler());
    }

    protected function exampleNewPath()
    {
        return Path::canonicalize($this->workspace->path(self::EXAMPLE_NEW_PATH));
    }
}
