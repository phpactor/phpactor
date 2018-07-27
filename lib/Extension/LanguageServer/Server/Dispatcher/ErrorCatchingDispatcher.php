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

    public function dispatch(array $request): ResponseMessage
    {
        try {
            return $this->innerDispatcher->dispatch($request);
        } catch (\Exception $e) {
            return new ResponseMessage(1, null, new ResponseError(ResponseError::InternalError, $e->getMessage(), $e->getTraceAsString()));
        }
    }
}
