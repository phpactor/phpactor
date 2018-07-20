<?php

namespace Phpactor\Extension\LanguageServer\Server\Dispatcher;

use Phpactor\Extension\LanguageServer\Protocol\ResponseMessage;
use Phpactor\Extension\LanguageServer\Server\Dispatcher;
use Psr\Log\LoggerInterface;

class PsrLoggingDispatcher implements Dispatcher
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Dispatcher
     */
    private $innerDispatcher;

    public function __construct(Dispatcher $innerDispatcher, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->innerDispatcher = $innerDispatcher;
    }

    public function dispatch(string $method, array $arguments): ResponseMessage
    {
        $this->logger->debug(sprintf('>> %s', $method), $arguments);

        $result = $this->innerDispatcher->dispatch($method, $arguments);

        $this->logger->debug(sprintf('<< %s', $method), (array) $result);

        return $result;
    }
}
