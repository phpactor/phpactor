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

    public function dispatch(array $request): ResponseMessage
    {
        $this->logger->debug('>> IN ', $request);

        $result = $this->innerDispatcher->dispatch($request);

        $this->logger->debug('<< OUT', (array) $result);

        return $result;
    }
}
