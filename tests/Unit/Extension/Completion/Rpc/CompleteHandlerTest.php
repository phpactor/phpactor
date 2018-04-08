<?php

namespace Phpactor\Tests\Unit\Extension\Completion\Rpc;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Completion\Application\Complete;
use Phpactor\Extension\Rpc\Response\ReturnResponse;
use Phpactor\Extension\Completion\Rpc\CompleteHandler;

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

        $this->assertInstanceOf(ReturnResponse::class, $action);
        $this->assertEquals([
            'aaa', 'bbb',
        ], $action->value());
    }
}
