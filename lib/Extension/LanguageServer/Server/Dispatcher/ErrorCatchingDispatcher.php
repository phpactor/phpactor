<?php

namespace Phpactor\Extension\LanguageServer\Server\Dispatcher;

use Phpactor\Extension\LanguageServer\Protocol\ResponseError;
use Phpactor\Extension\LanguageServer\Protocol\ResponseMessage;
use Phpactor\Extension\LanguageServer\Server\Dispatcher;

class ErrorCatchingDispatcher implements Dispatcher
{
    /**
     * @var Dispatcher
     */
    private $innerDispatcher;

    public function __construct(Dispatcher $innerDispatcher)
    {
        $this->innerDispatcher = $innerDispatcher;
    }

    public function dispatch(string $method, array $arguments): ResponseMessage
    {
        try {
            return $this->innerDispatcher->dispatch($method, $arguments);
        } catch (\Exception $e) {
            return new ResponseMessage(1, null, new ResponseError(ResponseError::InternalError, $e->getMessage(), $e->getTraceAsString()));
        }
    }
}
