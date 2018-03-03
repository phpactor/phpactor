<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use Phpactor\Tests\Unit\Rpc\Handler\HandlerTestCase;
use Phpactor\Rpc\Handler;
use Phpactor\Application\Status;
use Prophecy\Prophecy\ObjectProphecy;
use Phpactor\Rpc\Response\EchoResponse;
use Phpactor\Rpc\Handler\StatusHandler;
use Phpactor\Config\ConfigLoader;

class StatusHandlerTest extends HandlerTestCase
{
    /**
     * @var Status|ObjectProphecy
     */
    private $status;

    /**
     * @var ConfigLoader|ObjectProphecy
     */
    private $loader;

    public function setUp()
    {
        $this->status = $this->prophesize(Status::class);
        $this->loader = $this->prophesize(ConfigLoader::class);
    }

    public function createHandler(): Handler
    {
        return new StatusHandler(
            $this->status->reveal(),
            $this->loader->reveal()
        );
    }

    public function testStatus()
    {
        $this->status->check()->willReturn([
            'good' => [ 'i am good' ],
            'bad' => [ 'i am bad' ],
        ]);
        $this->loader->configFiles()->willReturn([
            'config/file1.yml',
            'config/file2.yml',
        ]);

        $response = $this->handle('status', []);
        $this->assertInstanceOf(EchoResponse::class, $response);
    }
}
