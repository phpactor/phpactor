<?php

namespace Phpactor\Extension\Rpc\RequestHandler;

use Phpactor\Extension\Rpc\RequestHandler;
use Phpactor\Extension\Rpc\Response\ErrorResponse;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\Rpc\Response;
use Exception;

class ExceptionCatchingHandler implements RequestHandler
{
    private RequestHandler $innerHandler;

    public function __construct(RequestHandler $innerHandler)
    {
        $this->innerHandler = $innerHandler;
    }

    public function handle(Request $request): Response
    {
        try {
            return $this->innerHandler->handle($request);
        } catch (Exception $exception) {
            return ErrorResponse::fromException($exception);
        }
    }
}
