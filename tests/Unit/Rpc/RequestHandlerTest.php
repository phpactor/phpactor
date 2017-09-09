<?php

namespace Phpactor\Tests\Unit\Rpc;

use PHPUnit\Framework\TestCase;
use Phpactor\Rpc\HandlerRegistry;
use Phpactor\Rpc\Handler;
use Phpactor\Rpc\RequestHandler;
use Phpactor\Rpc\Request;
use Phpactor\Rpc\Action;
use Phpactor\Rpc\Response;

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

        $request = Request::fromActions([
            Action::fromNameAndParameters('aaa', [
                'foo' => 'bar',
            ])
        ]);

        $response = $this->requestHandler->handle($request);
    }

    public function testHandle()
    {
        $expectedResponse = Response::fromActions([]);

        $this->handlerRegistry->get('aaa')->willReturn($this->handler->reveal());
        $this->handler->name()->willReturn('handler1');
        $this->handler->defaultParameters()->willReturn([
            'one' => 'foo',
        ]);

        $request = Request::fromActions([
            Action::fromNameAndParameters('aaa', [
                'one' => 'bar',
            ])
        ]);

        $this->handler->handle(['one' => 'bar'])->willReturn($expectedResponse);

        $response = $this->requestHandler->handle($request);

        $this->assertSame($expectedResponse, $response);
    }
}
