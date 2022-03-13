<?php

namespace Phpactor\Tests\Unit\Extension\CodeTransformExtra\Rpc;

use Phpactor\Extension\Rpc\Handler;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\Extension\Rpc\Response\Input\ListInput;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;
use Phpactor\CodeTransform\Domain\Refactor\GenerateAccessor;
use Phpactor\Extension\CodeTransformExtra\Rpc\GenerateAccessorHandler;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\Tests\Unit\Extension\Rpc\HandlerTestCase;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\ReflectorBuilder;
use Prophecy\Prophecy\ObjectProphecy;

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
    const BAR_NAME = 'bar';
    const PROPERTIES_CHOICES = [self::FOO_NAME => self::FOO_NAME, self::BAR_NAME => self::BAR_NAME];
    const GENERATE_ACCESSOR_ACTION = 'generate_accessor';
    const CURSOR_OFFSET = 57;

    private ObjectProphecy $generateAccessor;

    private Reflector $reflector;

    public function setUp(): void
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

    public function testSuggestsPossibleProperties(): void
    {
        $action = $this->handle(self::GENERATE_ACCESSOR_ACTION, [
            'source' => self::SOURCE,
            'path' => self::PATH,
            'offset' => self::CURSOR_OFFSET,
        ]);

        /** @var InputCallbackResponse $action */
        $this->assertInstanceOf(InputCallbackResponse::class, $action);

        $inputs = $action->inputs();
        $input = reset($inputs);

        /** @var ListInput $input */
        $this->assertInstanceOf(ListInput::class, $input);

        $this->assertEquals(self::PROPERTIES_CHOICES, $input->choices());
    }

    public function testGeneratesAccessorIfSpecificPropertyIsSelected(): void
    {
        [ $source, $offset ] = ExtractOffset::fromSource(
            <<<'EOT'
                <?php

                class
                {
                    private $<>foo;
                }
                EOT
        );

        $edits = TextEdits::fromTextEdits([TextEdit::create(ByteOffset::fromInt(0), 0, 'foobar')]);
        $this->generateAccessor->generate($source, ['foo'], $offset)
             ->willReturn($edits)
             ->shouldBeCalledTimes(1);

        $action = $this->handle(self::GENERATE_ACCESSOR_ACTION, [
            'source' => $source,
            'path' => self::PATH,
            'offset' => $offset,
        ]);

        /** @var InputCallbackResponse $action */
        $this->assertInstanceOf(UpdateFileSourceResponse::class, $action);
    }

    public function testGenerateAccessorFromAPropertyName(): void
    {
        $oldSource = SourceCode::fromStringAndPath(self::SOURCE, self::PATH);
        $newSource = SourceCode::fromStringAndPath('asd', self::PATH);

        $edits = TextEdits::fromTextEdits([
            TextEdit::create(
                0,
                mb_strlen(self::SOURCE),
                'asd'
            )
        ]);

        $this->generateAccessor->generate($oldSource, [self::FOO_NAME], self::CURSOR_OFFSET)
             ->willReturn($edits)
             ->shouldBeCalledTimes(1);

        $action = $this->handle(self::GENERATE_ACCESSOR_ACTION, [
            'source' => self::SOURCE,
            'path' => self::PATH,
            'names' => self::FOO_NAME,
            'offset' => self::CURSOR_OFFSET,
        ]);

        /** @var UpdateFileSourceResponse $action */
        $this->assertInstanceof(UpdateFileSourceResponse::class, $action);
        $this->assertSame((string) $oldSource, $action->oldSource());
        $this->assertSame((string) $newSource, $action->newSource());
        $this->assertSame(self::PATH, $action->path());
    }

    public function testGenerateAccessorsFromMultiplePropertyName(): void
    {
        $oldSource = SourceCode::fromStringAndPath(self::SOURCE, self::PATH);

        $temporarySource = SourceCode::fromStringAndPath('asd', self::PATH);

        $edits = TextEdits::fromTextEdits([
            TextEdit::create(
                0,
                mb_strlen(self::SOURCE),
                'asd'
            )
        ]);
        $this->generateAccessor->generate($oldSource, [
            self::FOO_NAME,
            self::BAR_NAME
        ], self::CURSOR_OFFSET)
             ->willReturn($edits)
             ->shouldBeCalledTimes(1);

        $newSource = SourceCode::fromStringAndPath((string) $temporarySource, self::PATH);

        $action = $this->handle(self::GENERATE_ACCESSOR_ACTION, [
            'source' => self::SOURCE,
            'path' => self::PATH,
            'names' => [self::FOO_NAME, self::BAR_NAME],
            'offset' => self::CURSOR_OFFSET,
        ]);

        /** @var UpdateFileSourceResponse $action */
        $this->assertInstanceof(UpdateFileSourceResponse::class, $action);
        $this->assertSame((string) $oldSource, $action->oldSource());
        $this->assertSame((string) $temporarySource, $action->newSource());
        $this->assertSame(self::PATH, $action->path());
    }
}
