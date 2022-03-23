<?php

namespace Phpactor\Extension\Rpc\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\Container\Container;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\Rpc\Command\RpcCommand;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\Rpc\RequestHandler;
use Phpactor\Extension\Rpc\Response;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;

class RpcExtensionTest extends TestCase
{
    public function testRpcCommand(): void
    {
        $container = $this->createContainer();
        $loader = $container->get(ConsoleExtension::SERVICE_COMMAND_LOADER);
        $this->assertInstanceOf(RpcCommand::class, $loader->get('rpc'));
    }

    public function testHandler(): void
    {
        $container = $this->createContainer();
        $handler = $this->getHandler($container);
        $response = $handler->handle(Request::fromNameAndParameters('echo', [
            'message' => 'world',
        ]));
        $this->assertInstanceOf(Response::class, $response);
    }

    private function getHandler(Container $container): RequestHandler
    {
        return $container->get(RpcExtension::SERVICE_REQUEST_HANDLER);
    }

    private function createContainer(): Container
    {
        $container = PhpactorContainer::fromExtensions([
            LoggingExtension::class,
            RpcExtension::class,
            ConsoleExtension::class,
            FilePathResolverExtension::class,
        ], []);
        return $container;
    }
}
