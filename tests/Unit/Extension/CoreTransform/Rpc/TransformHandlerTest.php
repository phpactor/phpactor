<?php

namespace Phpactor\Tests\Unit\Extension\CoreTransform\Rpc;

use Phpactor\Extension\Rpc\Handler;
use Phpactor\CodeTransform\Domain\Transformer;
use Phpactor\Extension\Rpc\Response\Input\ChoiceInput;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\CodeTransform\CodeTransform;
use Phpactor\Extension\CodeTransformExtra\Rpc\TransformHandler;
use Phpactor\CodeTransform\Domain\Transformers;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;
use Phpactor\Tests\Unit\Extension\Rpc\HandlerTestCase;

class TransformHandlerTest extends HandlerTestCase
{
    const SOURCE = '<?php echo "foo";';
    const TRANSFORMED_SOURCE = '<?php echo "bar";';
    const PATH = '/path/to.php';

    /**
     * @var Transformer
     */
    private $transformer;

    public function setUp()
    {
        $this->transformer = $this->prophesize(CodeTransform::class);
    }

    public function createHandler(): Handler
    {
        return new TransformHandler($this->transformer->reveal());
    }

    public function testDemandTransformation()
    {
        $this->transformer->transformers()->willReturn(
            Transformers::fromArray([
                'aaa' => $this->prophesize(Transformer::class)->reveal()
            ])
        );

        $action = $this->handle('transform', [
            'transform' => null,
            'source' => self::SOURCE,
            'path' => self::PATH,
        ]);

        $this->assertInstanceOf(InputCallbackResponse::class, $action);
        $inputs = $action->inputs();
        $this->assertCount(1, $inputs);
        $firstInput = reset($inputs);
        $this->assertInstanceOf(ChoiceInput::class, $firstInput);
        $this->assertEquals('transform', $action->callbackAction()->name());
        $this->assertEquals(['aaa' => 'aaa'], $firstInput->choices());
    }

    public function testTransform()
    {
        /**
         * @var $transformer Transformer */
        $transformer = $this->prophesize(Transformer::class);
        $this->transformer->transformers()->willReturn(
            Transformers::fromArray([
                'aaa' => $transformer->reveal()
            ])
        );
        $source = SourceCode::fromStringAndPath(self::SOURCE, self::PATH);
        $this->transformer->transform($source, [ 'aaa' ])->willReturn(SourceCode::fromString(self::TRANSFORMED_SOURCE));

        $action = $this->createHandler('transformer')->handle([
            'transform' => 'aaa',
            'source' => self::SOURCE,
            'path' => self::PATH,
        ]);

        $this->assertInstanceOf(UpdateFileSourceResponse::class, $action);
        $this->assertEquals(self::TRANSFORMED_SOURCE, $action->newSource());
        $this->assertEquals(self::PATH, $action->path());
    }
}
