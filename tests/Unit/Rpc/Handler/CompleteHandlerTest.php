<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use PHPUnit\Framework\TestCase;
use Phpactor\Application\Complete;
use Phpactor\Rpc\Editor\ReturnAction;
use Phpactor\Rpc\Handler\CompleteHandler;

class CompleteHandlerTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $complete;

    public function setUp()
    {
        $this->complete = $this->prophesize(Complete::class);
    }

    public function testHandler()
    {
        $handler = new CompleteHandler($this->complete->reveal());
        $this->complete->complete('aaa', 1234)->willReturn([
            'aaa', 'bbb',
        ]);
        $action = $handler->handle(['source' => 'aaa', 'offset' => 1234]);

        $this->assertInstanceOf(ReturnAction::class, $action);
        $this->assertEquals([
            'aaa', 'bbb',
        ], $action->value());

    }
}

