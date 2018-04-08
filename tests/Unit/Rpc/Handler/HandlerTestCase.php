<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\Rpc\HandlerRegistry;
use Phpactor\Extension\Rpc\RequestHandler\RequestHandler;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response;

abstract class HandlerTestCase extends TestCase
{
    abstract protected function createHandler(): Handler;

    protected function handle(string $actionName, array $parameters): Response
    {
        $registry = new HandlerRegistry([
            $this->createHandler()
        ]);
        $requestHandler = new RequestHandler($registry);
        $request = Request::fromNameAndParameters($actionName, $parameters);

        return $requestHandler->handle($request);
    }
}
