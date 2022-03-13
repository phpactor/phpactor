<?php

namespace Phpactor\Extension\Rpc\Test;

use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Registry\ActiveHandlerRegistry;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\Rpc\RequestHandler\RequestHandler;

class HandlerTester
{
    private Handler $handler;

    public function __construct(Handler $handler)
    {
        $this->handler = $handler;
    }

    public function handle(string $actionName, array $parameters)
    {
        $registry = new ActiveHandlerRegistry([
            $this->handler
        ]);
        $requestHandler = new RequestHandler($registry);
        $request = Request::fromNameAndParameters($actionName, $parameters);

        return $requestHandler->handle($request);
    }
}
