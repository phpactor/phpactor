<?php

namespace Phpactor\Tests\Unit\Extension\CoreTransform\Rpc;

use Phpactor\Tests\Unit\Extension\Rpc\HandlerTestCase;
use Phpactor\Extension\CodeTransform\Rpc\OverrideMethodHandler;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\CodeTransform\Domain\Refactor\OverrideMethod;
use Phpactor\CodeTransform\Domain\SourceCode as TransformSourceCode;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;
use Phpactor\Extension\Rpc\Response\Input\ListInput;
use Phpactor\WorseReflection\ReflectorBuilder;

class OverrideMethodHandlerTest extends HandlerTestCase
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var ObjectProphecy
     */
    private $overrideMethod;

    public function setUp()
    {
        $this->reflector = ReflectorBuilder::create()->addSource('<?php class ParentClass { public function foobar() {} }')->build();
        $this->overrideMethod = $this->prophesize(OverrideMethod::class);
    }

    public function createHandler(): Handler
    {
        return new OverrideMethodHandler(
            $this->reflector,
            $this->overrideMethod->reveal()
        );
    }

    public function testSuggestsPossibleMethods()
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
        $this->assertCount(1, $choices);
    }

    public function testOverrideMethod()
    {
        $source = <<<'EOT'
<?php 

class ChildClass extends ParentClass
{
}
EOT
        ;

        $this->overrideMethod->overrideMethod($source, 'ChildClass', 'foobar')->willReturn(TransformSourceCode::fromString('hello'));

        $action = $this->handle('override_method', [
            'class_name' => 'ChildClass',
            'method_name' => 'foobar',
            'path' => __FILE__,
            'source' => $source
        ]);

        $this->assertInstanceOf(UpdateFileSourceResponse::class, $action);
        $this->assertEquals('hello', $action->newSource());
    }
}
