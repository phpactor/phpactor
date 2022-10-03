<?php

namespace Phpactor\Extension\Rpc\Tests\Unit;

use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\HandlerRegistry;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\Rpc\RequestHandler\RequestHandler;
use Phpactor\Extension\Rpc\Response;
use Phpactor\MapResolver\Resolver;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class RequestHandlerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $handlerRegistry;

    private ObjectProphecy $handler;

    private RequestHandler $requestHandler;

    public function setUp(): void
    {
        $this->handlerRegistry = $this->prophesize(HandlerRegistry::class);
        $this->handler = $this->prophesize(Handler::class);

        $this->requestHandler = new RequestHandler(
            $this->handlerRegistry->reveal()
        );
    }

    public function testHandle(): void
    {
        $expectedResponse = $this->prophesize(Response::class);

        $this->handlerRegistry->get('aaa')->willReturn($this->handler->reveal());
        $this->handler->configure(Argument::type(Resolver::class))->will(function ($args): void {
            $args[0]->setDefaults([
                'one' => null,
            ]);
        });
        ;

        $request = Request::fromNameAndParameters('aaa', [
            'one' => 'bar',
        ]);

        $this->handler->handle(['one' => 'bar'])->willReturn($expectedResponse->reveal());

        $response = $this->requestHandler->handle($request);

        $this->assertEquals($expectedResponse->reveal(), $response);
    }
}
