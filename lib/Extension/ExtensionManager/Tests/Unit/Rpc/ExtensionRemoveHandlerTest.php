<?php

namespace Phpactor\Extension\ExtensionManager\Tests\Unit\Rpc;

use Exception;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\ExtensionManager\Rpc\ExtensionRemoveHandler;
use Phpactor\Extension\ExtensionManager\Service\RemoverService;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Rpc\Response\ErrorResponse;
use Phpactor\Extension\Rpc\Response\InputCallbackResponse;
use Phpactor\Extension\Rpc\Test\HandlerTester;

class ExtensionRemoveHandlerTest extends TestCase
{
    const EXAMPLE_EXTENSION_NAME = 'foo_extension';

    /**
     * @var ObjectProphecy
     */
    private $remover;

    public function setUp(): void
    {
        $this->remover = $this->prophesize(RemoverService::class);
    }

    public function testAsksForExtensionName(): void
    {
        $tester = $this->createTester();
        $response = $tester->handle('extension_remove', []);
        $this->assertInstanceOf(InputCallbackResponse::class, $response);
    }

    public function testRemovesExtension(): void
    {
        $tester = $this->createTester();
        $this->remover->removeExtension(self::EXAMPLE_EXTENSION_NAME)->shouldBeCalled();
        $response = $tester->handle('extension_remove', [
            'extension_name' => self::EXAMPLE_EXTENSION_NAME,
        ]);
        $this->assertInstanceOf(EchoResponse::class, $response);
    }

    public function testShowsErrorIfExtensionFailedToBeRemoved(): void
    {
        $tester = $this->createTester();
        $this->remover->removeExtension(self::EXAMPLE_EXTENSION_NAME)->willThrow(new Exception('sorry'));
        $response = $tester->handle('extension_remove', [
            'extension_name' => self::EXAMPLE_EXTENSION_NAME,
        ]);
        $this->assertInstanceOf(ErrorResponse::class, $response);
    }

    private function createTester(): HandlerTester
    {
        $tester = new HandlerTester(
            new ExtensionRemoveHandler($this->remover->reveal())
        );
        return $tester;
    }
}
