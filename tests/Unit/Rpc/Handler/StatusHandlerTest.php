<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use Phpactor\Tests\Unit\Rpc\Handler\HandlerTestCase;
use Phpactor\Rpc\Handler;
use Phpactor\Extension\Core\Application\Status;
use Prophecy\Prophecy\ObjectProphecy;
use Phpactor\Rpc\Response\EchoResponse;
use Phpactor\Extension\Core\Rpc\StatusHandler;
use Phpactor\Config\Paths;

class StatusHandlerTest extends HandlerTestCase
{
    /**
     * @var Status|ObjectProphecy
     */
    private $status;

    /**
     * @var ObjectProphecy
     */
    private $paths;

    public function setUp()
    {
        $this->status = $this->prophesize(Status::class);
        $this->paths = $this->prophesize(Paths::class);
    }

    public function createHandler(): Handler
    {
        return new StatusHandler(
            $this->status->reveal(),
            $this->paths->reveal()
        );
    }

    public function testStatus()
    {
        $this->status->check()->willReturn([
            'good' => [ 'i am good' ],
            'bad' => [ 'i am bad' ],
        ]);
        $this->paths->configFiles()->willReturn([
            'config/file1.yml',
            'config/file2.yml',
        ]);

        $response = $this->handle('status', []);
        $this->assertInstanceOf(EchoResponse::class, $response);
    }
}
