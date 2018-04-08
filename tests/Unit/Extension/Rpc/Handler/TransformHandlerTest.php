<?php

namespace Phpactor\Tests\Unit\Extension\Rpc\Handler;

use Phpactor\Extension\Rpc\Handler;
use Phpactor\CodeTransform\Domain\Transformer;
use Phpactor\Extension\Rpc\Response\Input\ChoiceInput;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\CodeTransform\CodeTransform;
use Phpactor\Extension\CodeTransform\Rpc\TransformHandler;
use Phpactor\CodeTransform\Domain\Transformers;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\Rpc\Response\ReplaceFileSourceResponse;
use Phpactor\Tests\Unit\Extension\Rpc\Handler\HandlerTestCase;

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

        $action = $this->createHandler('transformer')->handle([
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
        $source = SourceCode::fromString(self::SOURCE);
        $this->transformer->transform($source, [ 'aaa' ])->willReturn(SourceCode::fromString(self::TRANSFORMED_SOURCE));

        $action = $this->createHandler('transformer')->handle([
            'transform' => 'aaa',
            'source' => self::SOURCE,
            'path' => self::PATH,
        ]);

        $this->assertInstanceOf(ReplaceFileSourceResponse::class, $action);
        $this->assertEquals(self::TRANSFORMED_SOURCE, $action->replacementSource());
        $this->assertEquals(self::PATH, $action->path());
    }
}
