<?php

namespace Phpactor\Rpc\RequestHandler;

use Phpactor\Rpc\RequestHandler;
use Phpactor\Rpc\Response;
use Phpactor\Rpc\Request;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Phpactor\Rpc\Editor\ErrorAction;

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
        $this->logger->debug('REQUEST', $request->toArray());
        $response = $this->requestHandler->handle($request);

        $level = LogLevel::DEBUG;
        foreach ($response->actions() as $action) {
            if ($action instanceof ErrorAction) {
                $level = LogLevel::ERROR;
                break;
            }
        }

        $this->logger->log($level, 'RESPONSE', $response->toArray());

        return $response;
    }
}
