<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use Phpactor\Tests\Unit\Rpc\Handler\HandlerTestCase;
use Phpactor\Rpc\Handler;
use Phpactor\Application\Status;
use Prophecy\Prophecy\ObjectProphecy;
use Phpactor\Rpc\Response\EchoResponse;
use Phpactor\Rpc\Handler\StatusHandler;

class StatusHandlerTest extends HandlerTestCase
{
    /**
     * @var Status|ObjectProphecy
     */
    private $status;

    public function setUp()
    {
        $this->status = $this->prophesize(Status::class);
    }

    public function createHandler(): Handler
    {
        return new StatusHandler($this->status->reveal());
    }

    public function testStatus()
    {
        $this->status->check()->willReturn([
            'good' => [ 'i am good' ],
            'bad' => [ 'i am bad' ],
        ]);

        $response = $this->handle('status', []);
        $this->assertInstanceOf(EchoResponse::class, $response);
    }
}
