<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use PHPUnit\Framework\TestCase;
use Phpactor\Tests\Unit\Rpc\Handler\HandlerTestCase;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StringSourceLocator;
use Phpactor\Rpc\Handler\OverloadMethodHandler;
use Phpactor\Rpc\Handler;
use Phpactor\WorseReflection\Core\Logger\ArrayLogger;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\Rpc\Response\Input\ChoiceInput;
use Phpactor\CodeTransform\Domain\Refactor\OverloadMethod;
use Phpactor\CodeTransform\Domain\SourceCode as TransformSourceCode;
use Phpactor\Rpc\Response\ReplaceFileSourceResponse;

class OverloadMethodHandlerTest extends HandlerTestCase
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var ObjectProphecy
     */
    private $overloadMethod;

    public function setUp()
    {
        $this->reflector = Reflector::create(
            new StringSourceLocator(SourceCode::fromString('<?php class ParentClass { public function foobar() {} }'))
        );
        $this->overloadMethod = $this->prophesize(OverloadMethod::class);
    }

    public function createHandler(): Handler
    {
        return new OverloadMethodHandler(
            $this->reflector,
            $this->overloadMethod->reveal()
        );
    }

    public function testSuggestsPossibleClasses()
    {
        $action = $this->handle('overload_method', [
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
        $this->assertInstanceOf(ChoiceInput::class, $input);
        $choices = $input->choices();
        $this->assertCount(1, $choices);
    }

    public function testOverloadMethod()
    {
        $source = <<<'EOT'
<?php 

class ChildClass extends ParentClass
{
}
EOT
        ;

        $this->overloadMethod->overloadMethod($source, 'ChildClass', 'foobar')->willReturn(TransformSourceCode::fromString('hello'));

        $action = $this->handle('overload_method', [
            'class_name' => 'ChildClass',
            'method_name' => 'foobar',
            'path' => __FILE__,
            'source' => $source 
        ]);

        $this->assertInstanceOf(ReplaceFileSourceResponse::class, $action);
        $this->assertEquals('hello', $action->replacementSource());
    }
}
