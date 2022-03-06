<?php

namespace Phpactor\Extension\Rpc\RequestHandler;

use Phpactor\Extension\Rpc\RequestHandler;
use Phpactor\Extension\Rpc\Response;
use Phpactor\Extension\Rpc\Request;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Phpactor\Extension\Rpc\Response\ErrorResponse;

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
    ) {
        $this->requestHandler = $requestHandler;
        $this->logger = $logger;
    }

    public function handle(Request $request): Response
    {
        $this->logger->debug('REQUEST', [
            'action' => $request->name(),
            'parameters' => $request->parameters()
        ]);

        $response = $this->requestHandler->handle($request);

        $level = LogLevel::DEBUG;
        if ($response instanceof ErrorResponse) {
            $level = LogLevel::ERROR;
        }

        $this->logger->log($level, 'RESPONSE', [
            'action' => $response->name(),
            'parameters' => $response->parameters(),
        ]);

        return $response;
    }
}
