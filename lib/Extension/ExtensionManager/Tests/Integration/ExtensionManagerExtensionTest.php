<?php

namespace Phpactor\Extension\ExtensionManager\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\ExtensionManager\ExtensionManagerExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\Rpc\Request;
use Phpactor\Extension\Rpc\RequestHandler;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\FilePathResolverExtension\FilePathResolverExtension;
use Phpactor\TestUtils\Workspace;

class ExtensionManagerExtensionTest extends TestCase
{
    /**
     * @var Workspace
     */
    private $workspace;

    public function setUp(): void
    {
        $this->workspace = Workspace::create(__DIR__ . '/../Workspace');
        $this->workspace->reset();
    }

    public function testRegistersRpcHandlers(): void
    {
        $this->markTestSkipped('Skip due to failing test on github actions');
        $container = $this->loadContainer();
        /** @var RequestHandler $handler */
        $handler = $container->get(RpcExtension::SERVICE_REQUEST_HANDLER);
        $response = $handler->handle(Request::fromNameAndParameters('extension_list', []));
        $this->assertInstanceOf(EchoResponse::class, $response);
    }

    private function loadContainer()
    {
        return PhpactorContainer::fromExtensions([
            ExtensionManagerExtension::class,
            ConsoleExtension::class,
            RpcExtension::class,
            LoggingExtension::class,
            FilePathResolverExtension::class
        ], [
            ExtensionManagerExtension::PARAM_EXTENSION_CONFIG_FILE => $this->workspace->path('extensions.json'),
            ExtensionManagerExtension::PARAM_VENDOR_DIR => __DIR__ . '/../../vendor',
            ExtensionManagerExtension::PARAM_EXTENSION_VENDOR_DIR => $this->workspace->path('extensions'),
            ExtensionManagerExtension::PARAM_INSTALLED_EXTENSIONS_FILE => $this->workspace->path('extensions/installed.cache.php'),
        ]);
    }
}
