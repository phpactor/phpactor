<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use Phpactor\Tests\Unit\Rpc\Handler\HandlerTestCase;
use Phpactor\Rpc\Handler;
use Phpactor\Rpc\Handler\ClassInflectHandler;
use Phpactor\Application\ClassInflect;
use Phpactor\Rpc\Editor\InputCallbackAction;
use Phpactor\Rpc\Editor\Input\ChoiceInput;
use Phpactor\Rpc\Editor\Input\TextInput;
use Phpactor\Application\Exception\FileAlreadyExists;
use Phpactor\Rpc\Editor\OpenFileAction;
use Phpactor\Rpc\Editor\EchoAction;
use Phpactor\Rpc\Editor\Input\ConfirmInput;

class ClassInflectHandlerTest extends HandlerTestCase
{
    const CURRENT_PATH = '/path/to.php';
    const NEW_PATH = '/path/to/new.php';
    const VARIANT = 'default';

    /**
     * @var ClassInflect
     */
    private $classInflect;

    public function setUp()
    {
        $this->classInflect = $this->prophesize(ClassInflect::class);
    }

    public function createHandler(): Handler
    {
        return new ClassInflectHandler($this->classInflect->reveal());
    }

    public function testDemandNewPathAndVariant()
    {
        $this->classInflect->availableGenerators()->willReturn([
            'A', 'B'
        ]);

        $action = $this->handle('class_inflect', [
            'current_path' => self::CURRENT_PATH
        ]);

        $this->assertInstanceOf(InputCallbackAction::class, $action);
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
        $this->classInflect->generateFromExisting(
            self::CURRENT_PATH, self::NEW_PATH, self::VARIANT, false
        )->willThrow(new FileAlreadyExists(self::NEW_PATH));

        $action = $this->handle('class_inflect', [
            'current_path' => self::CURRENT_PATH,
            'new_path' => self::NEW_PATH,
            'variant' => self::VARIANT,
        ]);

        $this->assertInstanceOf(InputCallbackAction::class, $action);
        $inputs = $action->inputs();
        $firstInput = reset($inputs);

        $this->assertInstanceOf(ConfirmInput::class, $firstInput);
        $this->assertEquals('overwrite', $firstInput->name());
    }

    public function testNoOverwrite()
    {
        $action = $this->handle('class_inflect', [
            'current_path' => self::CURRENT_PATH,
            'new_path' => self::NEW_PATH,
            'variant' => self::VARIANT,
            'overwrite' => false,
        ]);

        $this->assertInstanceOf(EchoAction::class, $action);
    }

    public function testGenerate()
    {
        $this->classInflect->generateFromExisting(
            self::CURRENT_PATH, self::NEW_PATH, self::VARIANT, false
        )->willReturn(self::NEW_PATH);

        $action = $this->handle('class_inflect', [
            'current_path' => self::CURRENT_PATH,
            'new_path' => self::NEW_PATH,
            'variant' => self::VARIANT,
        ]);

        $this->assertInstanceOf(OpenFileAction::class, $action);
        $this->assertEquals(self::NEW_PATH, $action->path());
    }
}

