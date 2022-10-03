<?php

namespace Phpactor\Tests\Unit\Extension\Rpc;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Registry\ActiveHandlerRegistry;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\Rpc\RequestHandler\RequestHandler;
use Phpactor\Extension\Rpc\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

abstract class HandlerTestCase extends TestCase
{
    use ArraySubsetAsserts;
    use ProphecyTrait;

    abstract protected function createHandler(): Handler;

    protected function handle(string $actionName, array $parameters): Response
    {
        $registry = new ActiveHandlerRegistry([
            $this->createHandler()
        ]);
        $requestHandler = new RequestHandler($registry);
        $request = Request::fromNameAndParameters($actionName, $parameters);

        return $requestHandler->handle($request);
    }
}
