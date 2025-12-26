<?php

namespace Phpactor\Extension\Rpc\RequestHandler;

use Phpactor\Extension\Rpc\RequestHandler;
use Phpactor\Extension\Rpc\Response\ErrorResponse;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\Rpc\Response;
use Exception;

class ExceptionCatchingHandler implements RequestHandler
{
    public function __construct(private readonly RequestHandler $innerHandler)
    {
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
