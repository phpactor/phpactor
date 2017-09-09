<?php

namespace Phpactor\Tests\Unit\Rpc;

use PHPUnit\Framework\TestCase;
use Phpactor\Rpc\HandlerRegistry;
use Phpactor\Rpc\Handler;
use Phpactor\Rpc\RequestHandler;
use Phpactor\Rpc\Request;
use Phpactor\Rpc\Action;
use Phpactor\Rpc\Response;
use Phpactor\Rpc\ActionRequest;

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
            ActionRequest::fromNameAndParameters('aaa', [
                'foo' => 'bar',
            ])
        ]);

        $response = $this->requestHandler->handle($request);
    }

    public function testHandle()
    {
        $action = $this->prophesize(Action::class);
        $expectedResponse = Response::fromActions([$action->reveal()]);

        $this->handlerRegistry->get('aaa')->willReturn($this->handler->reveal());
        $this->handler->name()->willReturn('handler1');
        $this->handler->defaultParameters()->willReturn([
            'one' => 'foo',
        ]);

        $request = Request::fromActions([
            ActionRequest::fromNameAndParameters('aaa', [
                'one' => 'bar',
            ])
        ]);

        $this->handler->handle(['one' => 'bar'])->willReturn($action->reveal());

        $response = $this->requestHandler->handle($request);

        $this->assertEquals($expectedResponse, $response);
    }
}
