<?php

namespace Phpactor\Tests\Unit\Rpc\Handler;

use PHPUnit\Framework\TestCase;
use Phpactor\Rpc\Request;
use Phpactor\Rpc\HandlerRegistry;
use Phpactor\Rpc\RequestHandler\RequestHandler;
use Phpactor\Rpc\Handler;
use Phpactor\Rpc\Action;

abstract class HandlerTestCase extends TestCase
{
    abstract protected function createHandler(): Handler;

    protected function handle(string $actionName, array $parameters): Action
    {
        $registry = new HandlerRegistry([
            $this->createHandler()
        ]);
        $requestHandler = new RequestHandler($registry);
        $request = Request::fromNameAndParameters($actionName, $parameters);

        $response = $requestHandler->handle($request);
        $actions = $response->actions();

        $this->assertCount(1, $actions);

        return reset($actions);
    }
}
