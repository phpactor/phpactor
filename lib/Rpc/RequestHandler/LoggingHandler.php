<?php

namespace Phpactor\Rpc\RequestHandler;

use Phpactor\Rpc\RequestHandler;
use Phpactor\Rpc\Response;
use Phpactor\Rpc\Request;
use Psr\Log\LoggerInterface;

class LoggingHandler implements RequestHandler
{
    /**
     * @var RequestHandler
     */
    private $requestHandler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        RequestHandler $requestHandler,
        LoggerInterface $logger
    )
    {
        $this->requestHandler = $requestHandler;
        $this->logger = $logger;
    }

    public function handle(Request $request): Response
    {
        $this->logger->debug('REQ: ' . json_encode($request->toArray()));
        $response = $this->requestHandler->handle($request);
        $this->logger->debug('RES: ' . json_encode($response->toArray()));

        return $response;
    }
}
