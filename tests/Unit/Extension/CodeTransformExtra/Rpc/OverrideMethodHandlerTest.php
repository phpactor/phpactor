<?php

namespace Phpactor\Tests\Unit\Extension\CodeTransformExtra\Rpc;

use Phpactor\Tests\Unit\Extension\Rpc\HandlerTestCase;
use Phpactor\Extension\CodeTransformExtra\Rpc\OverrideMethodHandler;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\CodeTransform\Domain\Refactor\OverrideMethod;
use Phpactor\CodeTransform\Domain\SourceCode as TransformSourceCode;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;
use Phpactor\Extension\Rpc\Response\Input\ListInput;
use Phpactor\TextDocument\TextEdit as PhpactorTextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\ReflectorBuilder;
use Prophecy\Prophecy\ObjectProphecy;

class OverrideMethodHandlerTest extends HandlerTestCase
{
    private Reflector $reflector;

    private ObjectProphecy $overrideMethod;

    public function setUp(): void
    {
        $this->reflector = ReflectorBuilder::create()->addSource('<?php class ParentClass { public function foobar() {}  public function barfoor() {} }')->build();
        $this->overrideMethod = $this->prophesize(OverrideMethod::class);
    }

    public function createHandler(): Handler
    {
        return new OverrideMethodHandler(
            $this->reflector,
            $this->overrideMethod->reveal()
        );
    }

    public function testSuggestsPossibleMethods(): void
    {
        $action = $this->handle('override_method', [
            'class_name' => 'ChildClass',
            'path' => __FILE__,
            'source' => <<<'EOT'
                <?php

                class ChildClass extends ParentClass
                {
                }
                EOT
        ]);

        $input = $action->inputs();
        $input = reset($input);
        $this->assertInstanceOf(ListInput::class, $input);
        $choices = $input->choices();
        $this->assertCount(2, $choices);
    }

    public function testOverrideAMethodGivenAsAString(): void
    {
        $source = <<<'EOT'
            <?php

            class ChildClass extends ParentClass
            {
            }
            EOT
        ;

        $this->overrideMethod->overrideMethod(
            $source,
            'ChildClass',
            'foobar'
        )->willReturn(TextEdits::fromTextEdits([PhpactorTextEdit::create(0, strlen($source), 'hello')]));

        $action = $this->handle('override_method', [
            'class_name' => 'ChildClass',
            'method_name' => 'foobar',
            'path' => __FILE__,
            'source' => $source
        ]);

        $this->assertInstanceOf(UpdateFileSourceResponse::class, $action);
        $this->assertEquals('hello', $action->newSource());
    }

    public function testOverrideMethodsGivenAsArray(): void
    {
        $source = <<<'EOT'
            <?php

            class ChildClass extends ParentClass
            {
            }
            EOT
        ;

        $foobarTransformedCode = TransformSourceCode::fromString('foobar was added');
        $barfooTransformedCode = TransformSourceCode::fromString('barfoo was also added');

        $this->overrideMethod->overrideMethod($source, 'ChildClass', 'foobar')
            ->willReturn(TextEdits::fromTextEdits([PhpactorTextEdit::create(0, strlen($source), $foobarTransformedCode)]))
            ->shouldBeCalledTimes(1);
        $this->overrideMethod->overrideMethod($foobarTransformedCode, 'ChildClass', 'barfoo')
            ->willReturn(TextEdits::fromTextEdits([PhpactorTextEdit::create(0, strlen($foobarTransformedCode), $barfooTransformedCode)]))
            ->shouldBeCalledTimes(1);

        $action = $this->handle('override_method', [
            'class_name' => 'ChildClass',
            'method_name' => ['foobar', 'barfoo'],
            'path' => __FILE__,
            'source' => $source
        ]);

        /** @var UpdateFileSourceResponse $action */
        $this->assertInstanceOf(UpdateFileSourceResponse::class, $action);
        $this->assertEquals((string) $barfooTransformedCode, $action->newSource());
    }
}
