<?php

namespace Phpactor\Extension\Rpc\Tests\Unit\RequestHandler;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Rpc\RequestHandler;
use Phpactor\Extension\Rpc\RequestHandler\ExceptionCatchingHandler;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\Rpc\Response;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\PhpUnit\ProphecyTrait;
use Phpactor\Extension\Rpc\Response\ErrorResponse;
use Exception;

class ExceptionCatchingHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<RequestHandler> */
    private ObjectProphecy $innerHandler;

    private ExceptionCatchingHandler $exceptionHandler;

    /** @var ObjectProphecy<Response> */
    private ObjectProphecy $response;

    /** @var ObjectProphecy<Request> */
    private ObjectProphecy $request;

    public function setUp(): void
    {
        $this->innerHandler = $this->prophesize(RequestHandler::class);
        $this->exceptionHandler = new ExceptionCatchingHandler($this->innerHandler->reveal());
        $this->request = $this->prophesize(Request::class);
        $this->response = $this->prophesize(Response::class);
    }

    public function testDelegate(): void
    {
        $this->innerHandler->handle($this->request->reveal())->willReturn($this->response->reveal());

        $response = $this->exceptionHandler->handle($this->request->reveal());

        $this->assertSame(
            $this->response->reveal(),
            $response
        );
    }

    public function testCatchExceptions(): void
    {
        $this->innerHandler->handle(
            $this->request->reveal()
        )->willThrow(new Exception('Test!'));

        $response = $this->exceptionHandler->handle($this->request->reveal());

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertEquals('Test!', $response->message());
    }
}
