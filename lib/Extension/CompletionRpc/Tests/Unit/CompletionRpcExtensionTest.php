<?php

namespace Phpactor\Extension\CompletionRpc\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\CompletionRpc\CompletionRpcExtension;
use Phpactor\Extension\Completion\CompletionExtension;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\Rpc\RequestHandler;
use Phpactor\Extension\Rpc\Response\ReturnResponse;
use Phpactor\Extension\Rpc\RpcExtension;

class CompletionRpcExtensionTest extends TestCase
{
    public function testAddsCompletionHandler(): void
    {
        $handler = $this->createRequestHandler();
        $response = $handler->handle(Request::fromNameAndParameters('complete', [
            'source' => '',
            'offset' => 1,
        ]));
        $this->assertInstanceOf(ReturnResponse::class, $response);
    }

    private function createRequestHandler(): RequestHandler
    {
        $container = PhpactorContainer::fromExtensions([
            CompletionRpcExtension::class,
            RpcExtension::class,
            CompletionExtension::class,
            LoggingExtension::class,
        ]);
        
        return $container->get(RpcExtension::SERVICE_REQUEST_HANDLER);
    }
}
