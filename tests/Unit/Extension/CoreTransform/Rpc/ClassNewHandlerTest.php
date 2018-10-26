<?php

namespace Phpactor\Tests\Unit\Extension\CoreTransform\Rpc;

use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\CodeTransform\Rpc\ClassNewHandler;
use Phpactor\Extension\CodeTransform\Application\ClassNew;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\Extension\Rpc\Response\Input\ChoiceInput;
use Phpactor\Extension\Rpc\Response\Input\TextInput;
use Phpactor\Extension\CodeTransform\Application\Exception\FileAlreadyExists;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\Response\Input\ConfirmInput;
use Phpactor\Extension\Rpc\Response\ReplaceFileSourceResponse;
use Phpactor\Tests\Unit\Extension\Rpc\Handler\HandlerTestCase;

class ClassNewHandlerTest extends HandlerTestCase
{
    const CURRENT_PATH = '/path/to.php';
    const NEW_PATH = '/path/to/new.php';
    const VARIANT = 'default';

    /**
     * @var ClassNew
     */
    private $classNew;

    public function setUp()
    {
        $this->classNew = $this->prophesize(ClassNew::class);
    }

    public function createHandler(): Handler
    {
        return new ClassNewHandler($this->classNew->reveal());
    }

    public function testDemandNewPathAndVariant()
    {
        $this->classNew->availableGenerators()->willReturn([
            'A', 'B'
        ]);

        $action = $this->handle('class_new', [
            'current_path' => self::CURRENT_PATH
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $action);
        $inputs = $action->inputs();
        $firstInput = array_shift($inputs);
        $this->assertInstanceOf(ChoiceInput::class, $firstInput);
        $this->assertEquals('variant', $firstInput->name());
        $this->assertEquals([
            'A' => 'A', 'B' => 'B',
        ], $firstInput->choices());

        $secondInput = array_shift($inputs);
        $this->assertInstanceOf(TextInput::class, $secondInput);
        $this->assertEquals('new_path', $secondInput->name());
        $this->assertEquals(self::CURRENT_PATH, $secondInput->default());
    }

    public function testFileExists()
    {
        $this->classNew->generate(
            self::NEW_PATH,
            self::VARIANT,
            false
        )->willThrow(new FileAlreadyExists(self::NEW_PATH));

        $action = $this->handle('class_new', [
            'current_path' => self::CURRENT_PATH,
            'new_path' => self::NEW_PATH,
            'variant' => self::VARIANT,
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $action);
        $inputs = $action->inputs();
        $firstInput = reset($inputs);

        $this->assertInstanceOf(ConfirmInput::class, $firstInput);
        $this->assertEquals('overwrite', $firstInput->name());
    }

    public function testNoOverwrite()
    {
        $action = $this->handle('class_new', [
            'current_path' => self::CURRENT_PATH,
            'new_path' => self::NEW_PATH,
            'variant' => self::VARIANT,
            'overwrite' => false,
        ]);

        $this->assertInstanceOf(EchoResponse::class, $action);
    }

    public function testGenerate()
    {
        $this->classNew->generate(
            self::NEW_PATH,
            self::VARIANT,
            false
        )->willReturn(SourceCode::fromStringAndPath('fo', self::NEW_PATH));

        $action = $this->handle('class_new', [
            'current_path' => self::CURRENT_PATH,
            'new_path' => self::NEW_PATH,
            'variant' => self::VARIANT,
        ]);

        $this->assertInstanceOf(ReplaceFileSourceResponse::class, $action);
        $this->assertEquals(self::NEW_PATH, $action->path());
    }
}
