<?php

namespace Phpactor\Extension\Rpc\Test;

use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Registry\ActiveHandlerRegistry;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\Rpc\RequestHandler\RequestHandler;

class HandlerTester
{
    public function __construct(private readonly Handler $handler)
    {
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
