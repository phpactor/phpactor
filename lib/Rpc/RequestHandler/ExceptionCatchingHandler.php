<?php

namespace Phpactor\Rpc\RequestHandler;

use Phpactor\Rpc\RequestHandler;
use Phpactor\Rpc\Response;
use Phpactor\Rpc\Editor\ErrorAction;
use Phpactor\Rpc\ActionRequest;

class ExceptionCatchingHandler implements RequestHandler
{
    /**
     * @var RequestHandler
     */
    private $innerHandler;

    public function __construct(RequestHandler $innerHandler)
    {
        $this->innerHandler = $innerHandler;
    }

    public function handle(ActionRequest $request): Response
    {
        try {
            return $this->innerHandler->handle($request);
        } catch (\Exception $e) {
            return Response::fromActions([
                ErrorAction::fromMessageAndDetails($e->getMessage(), $e->getTraceAsString())
            ]);
        }
    }
}
