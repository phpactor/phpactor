<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use Phpactor\Tests\Unit\Rpc\Handler\HandlerTestCase;
use Phpactor\Rpc\Handler;
use Phpactor\Application\Status;
use Prophecy\Prophecy\ObjectProphecy;
use Phpactor\Rpc\Response\EchoResponse;
use Phpactor\Rpc\Handler\ConfigHandler;
use Phpactor\Config\ConfigLoader;
use Phpactor\Rpc\Response\InformationResponse;

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
