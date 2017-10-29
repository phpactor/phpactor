<?php

namespace Phpactor\Tests\Unit\Rpc\RequestHandler;

use PHPUnit\Framework\TestCase;
use Phpactor\Rpc\RequestHandler;
use Phpactor\Rpc\RequestHandler\ExceptionCatchingHandler;
use Phpactor\Rpc\Request;
use Phpactor\Rpc\Response;
use Prophecy\Prophecy\ObjectProphecy;
use Phpactor\Rpc\Editor\ErrorAction;

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

        $this->assertInstanceOf(ErrorAction::class, $response);
        $this->assertEquals('Test!', $response->message());
    }
}
