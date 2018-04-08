<?php

namespace Phpactor\Tests\Unit\Extension\Rpc\Handler;

use Phpactor\Extension\Rpc\Handler;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\Rpc\Response\ReplaceFileSourceResponse;
use Phpactor\CodeTransform\Domain\Refactor\GenerateAccessor;
use Phpactor\Extension\CodeTransform\Rpc\GenerateAccessorHandler;

class GenerateAccessorHandlerTest extends HandlerTestCase
{
    const SOURCE = '<?php echo "foo";';
    const PATH = '/path/to';
    const OFFSET = 1234;
    const CONSTANT_NAME = 'FOOBAR';

    /**
     * @var GenerateAccessor
     */
    private $generateAccessor;

    public function setUp()
    {
        $this->generateAccessor = $this->prophesize(GenerateAccessor::class);
    }

    public function createHandler(): Handler
    {
        return new GenerateAccessorHandler($this->generateAccessor->reveal());
    }

    public function testGenerateAccessor()
    {
        $this->generateAccessor->generateAccessor(
            self::SOURCE,
            self::OFFSET
        )->willReturn(SourceCode::fromStringAndPath('asd', '/path'));

        $action = $this->handle(GenerateAccessorHandler::NAME, [
            'source' => self::SOURCE,
            'path' => self::PATH,
            'offset' => self::OFFSET
        ]);

        $this->assertInstanceof(ReplaceFileSourceResponse::class, $action);
    }
}
