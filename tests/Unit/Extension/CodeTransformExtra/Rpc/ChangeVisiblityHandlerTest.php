<?php

namespace Phpactor\Tests\Unit\Extension\CodeTransformExtra\Rpc;

use Phpactor\CodeTransform\Domain\Refactor\ChangeVisiblity;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\CodeTransformExtra\Rpc\ChangeVisiblityHandler;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;
use Phpactor\Tests\Unit\Extension\Rpc\HandlerTestCase;
use Prophecy\Prophecy\ObjectProphecy;

class ChangeVisiblityHandlerTest extends HandlerTestCase
{
    const EXAMPLE_SOURCE = '<?php hello';
    const EXAMPLE_PATH = '/path/to';
    const EXAMPLE_OFFSET = 12;

    private ObjectProphecy $changeVisibility;

    public function setUp(): void
    {
        $this->changeVisibility = $this->prophesize(ChangeVisiblity::class);
    }

    public function testChangeVisiblity(): void
    {
        $expectedSource = SourceCode::fromStringAndPath(self::EXAMPLE_SOURCE, self::EXAMPLE_PATH);
        $this->changeVisibility->changeVisiblity($expectedSource, self::EXAMPLE_OFFSET)->willReturn($expectedSource);

        $response = $this->handle('change_visibility', [
            'source' => self::EXAMPLE_SOURCE,
            'path' => self::EXAMPLE_PATH,
            'offset' => self::EXAMPLE_OFFSET,
        ]);
        $this->assertInstanceof(UpdateFileSourceResponse::class, $response);
    }

    protected function createHandler(): Handler
    {
        return new ChangeVisiblityHandler($this->changeVisibility->reveal());
    }
}
