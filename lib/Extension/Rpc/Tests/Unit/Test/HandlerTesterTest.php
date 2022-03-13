<?php

namespace Phpactor\Extension\Rpc\Tests\Unit\Test;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Handler\EchoHandler;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\Test\HandlerTester;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\PhpUnit\ProphecyTrait;

class HandlerTesterTest extends TestCase
{
    use ProphecyTrait;

    private Handler $handler;

    private ObjectProphecy $response;

    public function setUp(): void
    {
        $this->handler = new EchoHandler();
    }

    public function testTester(): void
    {
        $tester = new HandlerTester($this->handler);

        $response = $tester->handle('echo', [ 'message' => 'bar' ]);
        $this->assertInstanceOf(EchoResponse::class, $response);
        $this->assertEquals('bar', $response->message());
    }
}
