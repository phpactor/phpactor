<?php

namespace Phpactor\Tests\Unit\Extension\Core\Rpc;

use Phpactor\Extension\Core\Rpc\ConfigHandler;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response\InformationResponse;
use Phpactor\Tests\Unit\Extension\Rpc\HandlerTestCase;

class ConfigHandlerTest extends HandlerTestCase
{
    public function createHandler(): Handler
    {
        return new ConfigHandler([
            'key1' => 'value1',
        ]);
    }

    public function testStatus(): void
    {
        $response = $this->handle('config', []);
        $this->assertInstanceOf(InformationResponse::class, $response);
    }
}
