<?php

namespace Phpactor\Tests\Unit\Extension\Rpc\RequestHandler;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Rpc\RequestHandler;
use Phpactor\Extension\Rpc\RequestHandler\ExceptionCatchingHandler;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\Rpc\Response;
use Prophecy\Prophecy\ObjectProphecy;
use Phpactor\Extension\Rpc\Response\ErrorResponse;

class ExceptionCatchingHandlerTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $innerHandler;

    /**
     * @var ExceptionCatchingHandler
     */
    private $exceptionHandler;

    /**
     * @var ObjectProphecy
     */
    private $response;

    /**
     * @var ObjectProphecy
     */
    private $request;

    public function setUp()
    {
        $this->innerHandler = $this->prophesize(RequestHandler::class);
        $this->exceptionHandler = new ExceptionCatchingHandler($this->innerHandler->reveal());
        $this->request = $this->prophesize(Request::class);
        $this->response = $this->prophesize(Response::class);
    }

    public function testDelegate()
    {
        $this->innerHandler->handle($this->request->reveal())->willReturn($this->response->reveal());

        $response = $this->exceptionHandler->handle($this->request->reveal());

        $this->assertSame(
            $this->response->reveal(),
            $response
        );
    }

    public function testCatchExceptions()
    {
        $this->innerHandler->handle(
            $this->request->reveal()
        )->willThrow(new \Exception('Test!'));

        $response = $this->exceptionHandler->handle($this->request->reveal());

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertEquals('Test!', $response->message());
    }
}
