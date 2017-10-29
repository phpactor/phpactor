<?php

namespace Phpactor\Tests\Unit\Rpc;

use PHPUnit\Framework\TestCase;
use Phpactor\Rpc\HandlerRegistry;
use Phpactor\Rpc\Handler;
use Phpactor\Rpc\RequestHandler\RequestHandler;
use Phpactor\Rpc\Response;
use Phpactor\Rpc\Request;
use Prophecy\Prophecy\ObjectProphecy;

class RequestHandlerTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $handlerRegistry;

    /**
     * @var ObjectProphecy
     */
    private $handler;

    /**
     * @var RequestHandler
     */
    private $requestHandler;

    public function setUp()
    {
        $this->handlerRegistry = $this->prophesize(HandlerRegistry::class);
        $this->handler = $this->prophesize(Handler::class);

        $this->requestHandler = new RequestHandler(
            $this->handlerRegistry->reveal()
        );
    }

    public function testInvalidArguments()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid arguments "foo" for handler "handler1", valid arguments: "bbb"');

        $this->handlerRegistry->get('aaa')->willReturn($this->handler->reveal());
        $this->handler->name()->willReturn('handler1');
        $this->handler->defaultParameters()->willReturn([
            'bbb' => 'ccc',
        ]);

        $request = Request::fromNameAndParameters('aaa', [
            'foo' => 'bar',
        ]);

        $response = $this->requestHandler->handle($request);
    }

    public function testHandle()
    {
        $expectedResponse = $this->prophesize(Response::class);

        $this->handlerRegistry->get('aaa')->willReturn($this->handler->reveal());
        $this->handler->name()->willReturn('handler1');
        $this->handler->defaultParameters()->willReturn([
            'one' => 'foo',
        ]);

        $request = Request::fromNameAndParameters('aaa', [
            'one' => 'bar',
        ]);

        $this->handler->handle(['one' => 'bar'])->willReturn($expectedResponse->reveal());

        $response = $this->requestHandler->handle($request);

        $this->assertEquals($expectedResponse->reveal(), $response);
    }
}
