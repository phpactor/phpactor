<?php

namespace Phpactor\Tests\Unit\Rpc\RequestHandler;

use PHPUnit\Framework\TestCase;
use Phpactor\Rpc\RequestHandler;
use Phpactor\Rpc\RequestHandler\ExceptionCatchingHandler;
use Phpactor\Rpc\Request;
use Phpactor\Rpc\Response;
use Prophecy\Prophecy\ObjectProphecy;
use Phpactor\Rpc\Editor\ErrorAction;
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
    }

    public function testLogging()
    {
        $requestData = [ 'one' => 'two' ];
        $responseData = [ 'three' => 'four' ];

        $this->request->toArray()->willReturn($requestData);
        $this->response->toArray()->willReturn($responseData);
        $this->response->actions()->willReturn([]);

        $this->innerHandler->handle($this->request->reveal())->willReturn($this->response->reveal());

        $response = $this->loggingHandler->handle($this->request->reveal());

        $this->assertSame(
            $this->response->reveal(),
            $response
        );

        $this->logger->debug('REQUEST', $requestData)->shouldHaveBeenCalled();
        $this->logger->log(LogLevel::DEBUG, 'RESPONSE', $responseData)->shouldHaveBeenCalled();
    }

    public function testLoggingWithError()
    {
        $requestData = [ 'one' => 'two' ];
        $responseData = [ 'three' => 'four' ];

        $this->request->toArray()->willReturn($requestData);
        $this->response->toArray()->willReturn($responseData);
        $this->response->actions()->willReturn([
            ErrorAction::fromMessageAndDetails('foobar', 'barfoo')
        ]);
        $this->innerHandler->handle($this->request->reveal())->willReturn($this->response->reveal());

        $response = $this->loggingHandler->handle($this->request->reveal());
        $this->logger->log(LogLevel::ERROR, 'RESPONSE', $responseData)->shouldHaveBeenCalled();
    }
}
