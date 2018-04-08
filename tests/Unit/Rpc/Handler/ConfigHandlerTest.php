<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use Phpactor\Tests\Unit\Rpc\Handler\HandlerTestCase;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Core\Application\Status;
use Prophecy\Prophecy\ObjectProphecy;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Core\Rpc\ConfigHandler;
use Phpactor\Config\ConfigLoader;
use Phpactor\Extension\Rpc\Response\InformationResponse;

class ConfigHandlerTest extends HandlerTestCase
{
    public function createHandler(): Handler
    {
        return new ConfigHandler([
            'key1' => 'value1',
        ]);
    }

    public function testStatus()
    {
        $response = $this->handle('config', []);
        $this->assertInstanceOf(InformationResponse::class, $response);
    }
}
