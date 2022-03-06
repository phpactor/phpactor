<?php

namespace Phpactor\Extension\ExtensionManager\Tests\Unit\Rpc;

use Exception;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\ExtensionManager\Rpc\ExtensionInstallHandler;
use Phpactor\Extension\ExtensionManager\Service\InstallerService;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\Response\ErrorResponse;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\Extension\Rpc\Test\HandlerTester;

class ExtensionInstallHandlerTest extends TestCase
{
    const EXAMPLE_EXTENSION_NAME = 'foo_extension';

    /**
     * @var ObjectProphecy
     */
    private $installer;

    public function setUp(): void
    {
        $this->installer = $this->prophesize(InstallerService::class);
    }

    public function testAsksForExtensionName(): void
    {
        $tester = $this->createTester();
        $response = $tester->handle('extension_install', []);
        $this->assertInstanceOf(InputCallbackResponse::class, $response);
    }

    public function testInstallsExtension(): void
    {
        $tester = $this->createTester();
        $this->installer->requireExtensions([ self::EXAMPLE_EXTENSION_NAME ])->shouldBeCalled();
        $response = $tester->handle('extension_install', [
            'extension_name' => self::EXAMPLE_EXTENSION_NAME,
        ]);
        $this->assertInstanceOf(EchoResponse::class, $response);
    }

    public function testShowsErrorIfExtensionFailedToInstall(): void
    {
        $tester = $this->createTester();
        $this->installer->requireExtensions([ self::EXAMPLE_EXTENSION_NAME ])->willThrow(new Exception('sorry'));
        $response = $tester->handle('extension_install', [
            'extension_name' => self::EXAMPLE_EXTENSION_NAME,
        ]);
        $this->assertInstanceOf(ErrorResponse::class, $response);
    }

    private function createTester(): HandlerTester
    {
        $tester = new HandlerTester(
            new ExtensionInstallHandler($this->installer->reveal())
        );
        return $tester;
    }
}
