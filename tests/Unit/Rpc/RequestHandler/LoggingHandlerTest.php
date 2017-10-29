<?php

namespace Phpactor\Tests\Unit\Rpc\RequestHandler;

use PHPUnit\Framework\TestCase;
use Phpactor\Rpc\RequestHandler;
use Phpactor\Rpc\RequestHandler\ExceptionCatchingHandler;
use Phpactor\Rpc\Request;
use Phpactor\Rpc\Response;
use Prophecy\Prophecy\ObjectProphecy;
use Phpactor\Rpc\Response\ErrorResponse;
use Psr\Log\LoggerInterface;
use Phpactor\Rpc\RequestHandler\LoggingHandler;
use Psr\Log\LogLevel;

class LoggingHandlerTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $innerHandler;

    /**
     * @var ExceptionCatchingHandler
     */
    private $loggingHandler;

    /**
     * @var ObjectProphecy
     */
    private $response;

    /**
     * @var ObjectProphecy
     */
    private $request;

    /**
     * @var ObjectProphecy
     */
    private $logger;

    public function setUp()
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->innerHandler = $this->prophesize(RequestHandler::class);

        $this->loggingHandler = new LoggingHandler($this->innerHandler->reveal(), $this->logger->reveal());

        $this->response = $this->prophesize(Response::class);
        $this->request = $this->prophesize(Request::class);
        $this->request->name()->willReturn('req-name');
        $this->request->parameters()->willReturn(['p1' => 'v1' ]);
        $this->response->name()->willReturn('res-name');
        $this->response->parameters()->willReturn(['p1' => 'v1' ]);

        $this->expectedRequestData = [ 'action' => 'req-name', 'parameters' => [ 'p1' => 'v1' ] ];
        $this->expectedResponseData = [ 'action' => 'res-name', 'parameters' => [ 'p1' => 'v1' ] ];
    }

    public function testLogging()
    {
        $this->innerHandler->handle($this->request->reveal())->willReturn($this->response->reveal());

        $response = $this->loggingHandler->handle($this->request->reveal());

        $this->assertSame(
            $this->response->reveal(),
            $response
        );

        $this->logger->debug('REQUEST', $this->expectedRequestData)->shouldHaveBeenCalled();
        $this->logger->log(LogLevel::DEBUG, 'RESPONSE', $this->expectedResponseData)->shouldHaveBeenCalled();
    }

    public function testLoggingWithError()
    {
        $response = ErrorResponse::fromMessageAndDetails('foobar', 'barfoo');
        $expected = [
            'action' => $response->name(),
            'parameters' => $response->parameters()
        ];

        $this->innerHandler->handle($this->request->reveal())->willReturn($response);

        $response = $this->loggingHandler->handle($this->request->reveal());
        $this->logger->log(LogLevel::ERROR, 'RESPONSE', $expected)->shouldHaveBeenCalled();
    }
}
