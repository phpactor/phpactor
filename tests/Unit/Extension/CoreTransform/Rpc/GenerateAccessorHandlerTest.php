<?php

namespace Phpactor\Tests\Unit\Extension\CoreTransform\Rpc;

use Phpactor\Extension\Rpc\Handler;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\Extension\Rpc\Response\Input\ListInput;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;
use Phpactor\CodeTransform\Domain\Refactor\GenerateAccessor;
use Phpactor\Extension\CodeTransformExtra\Rpc\GenerateAccessorHandler;
use Phpactor\Tests\Unit\Extension\Rpc\HandlerTestCase;
use Phpactor\WorseReflection\ReflectorBuilder;

class GenerateAccessorHandlerTest extends HandlerTestCase
{
    const SOURCE = <<<'PHP'
<?php

class Dummy
{
    private $foo;
    public $bar;
}
PHP;
    const PATH = '/path/to';
    const FOO_NAME = 'foo';
    const FOO_OFFSET = 33;
    const BAR_NAME = 'bar';
    const PROPERTIES_CHOICES = [self::FOO_NAME => self::FOO_NAME, self::BAR_NAME => self::BAR_NAME];
    const GENERATE_ACCESSOR_ACTION = 'generate_accessor';

    /**
     * @var GenerateAccessor
     */
    private $generateAccessor;

    /**
     * @var Reflector
     */
    private $reflector;

    public function setUp()
    {
        $this->reflector = ReflectorBuilder::create()->addSource(self::SOURCE)->build();
        $this->generateAccessor = $this->prophesize(GenerateAccessor::class);
    }

    public function createHandler(): Handler
    {
        return new GenerateAccessorHandler(
            $this->reflector,
            $this->generateAccessor->reveal()
        );
    }

    public function testGenerateAccessorFromOffset()
    {
        $this->generateAccessor->generateFromOffset(self::SOURCE, self::FOO_OFFSET)
             ->willReturn(SourceCode::fromStringAndPath('asd', '/path'))
             ->shouldBeCalledTimes(1);

        $action = $this->handle(self::GENERATE_ACCESSOR_ACTION, [
            'source' => self::SOURCE,
            'path' => self::PATH,
            'offset' => self::FOO_OFFSET
        ]);

        $this->assertInstanceof(UpdateFileSourceResponse::class, $action);
    }

    public function testSuggestsPossibleProperties()
    {
        $action = $this->handle(self::GENERATE_ACCESSOR_ACTION, [
            'source' => self::SOURCE,
            'path' => self::PATH,
        ]);

        /** @var InputCallbackResponse $action */
        $this->assertInstanceOf(InputCallbackResponse::class, $action);

        $inputs = $action->inputs();
        $input = reset($inputs);

        /** @var ListInput $input */
        $this->assertInstanceOf(ListInput::class, $input);

        $this->assertEquals(self::PROPERTIES_CHOICES, $input->choices());
    }

    public function testGenerateAccessorFromAPropertyName()
    {
        $oldSource = SourceCode::fromStringAndPath(self::SOURCE, self::PATH);
        $newSource = SourceCode::fromStringAndPath('asd', self::PATH);

        $this->generateAccessor->generateFromPropertyName($oldSource, self::FOO_NAME)
             ->willReturn($newSource)
             ->shouldBeCalledTimes(1);

        $action = $this->handle(self::GENERATE_ACCESSOR_ACTION, [
            'source' => self::SOURCE,
            'path' => self::PATH,
            'names' => [self::FOO_NAME],
        ]);

        /** @var UpdateFileSourceResponse $action */
        $this->assertInstanceof(UpdateFileSourceResponse::class, $action);
        $this->assertSame((string) $oldSource, $action->oldSource());
        $this->assertSame((string) $newSource, $action->newSource());
        $this->assertSame(self::PATH, $action->path());
    }

    public function testGenerateAccessorsFromMultiplePropertyName()
    {
        $oldSource = SourceCode::fromStringAndPath(self::SOURCE, self::PATH);

        $temporarySource = SourceCode::fromStringAndPath('asd', self::PATH);
        $this->generateAccessor->generateFromPropertyName($oldSource, self::FOO_NAME)
             ->willReturn($temporarySource)
             ->shouldBeCalledTimes(1);

        $newSource = SourceCode::fromStringAndPath((string) $temporarySource, self::PATH);
        $this->generateAccessor->generateFromPropertyName($temporarySource, self::BAR_NAME)
             ->willReturn($newSource)
             ->shouldBeCalledTimes(1);

        $action = $this->handle(self::GENERATE_ACCESSOR_ACTION, [
            'source' => self::SOURCE,
            'path' => self::PATH,
            'names' => [self::FOO_NAME, self::BAR_NAME],
        ]);

        /** @var UpdateFileSourceResponse $action */
        $this->assertInstanceof(UpdateFileSourceResponse::class, $action);
        $this->assertSame((string) $oldSource, $action->oldSource());
        $this->assertSame((string) $temporarySource, $action->newSource());
        $this->assertSame(self::PATH, $action->path());
    }
}
