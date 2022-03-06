<?php

namespace Phpactor\Extension\LanguageServer\Tests\Unit;

use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServerProtocol\CodeActionRequest;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Phpactor\LanguageServer\Core\Server\Exception\ExitSession;
use Phpactor\LanguageServer\Listener\WorkspaceListener;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use RuntimeException;
use function Amp\Promise\wait;
use function Amp\delay;

class LanguageServerExtensionTest extends LanguageServerTestCase
{
    public function testInitializesLanguageServer(): void
    {
        $serverTester = $this->createTester();
    }

    public function testLoadsTextDocuments(): void
    {
        $serverTester = $this->createTester();
        $serverTester->textDocument()->open(__FILE__, (string)file_get_contents(__FILE__));
    }

    public function testLoadsHandlers(): void
    {
        $serverTester = $this->createTester();
        $response = $serverTester->requestAndWait('test', []);
        $this->assertSuccess($response);
    }

    public function testReturnsStats(): void
    {
        $serverTester = $this->createTester();
        $response = $serverTester->requestAndWait('phpactor/stats', []);
        $this->assertSuccess($response);
        $message = $serverTester->transmitter()->shift();
        self::assertNotNull($message);
        assert($message instanceof NotificationMessage);
        self::assertStringContainsString('requests: 0', $message->params['message']);
    }

    public function testStartsServices(): void
    {
        $serverTester = $this->createTester(null, [
            LanguageServerExtension::PARAM_FILE_EVENTS =>false,
        ]);
        $serverTester->initialize();
        wait(delay(10));
        $message = $serverTester->transmitter()->shift();
        self::assertNotNull($message);
        assert($message instanceof NotificationMessage);
        self::assertEquals('service started', $message->params['message']);
    }

    public function testExit(): void
    {
        $this->expectException(ExitSession::class);

        $serverTester = $this->createTester();
        $serverTester->requestAndWait('exit', []);
    }

    public function testDebug(): void
    {
        $this->expectException(ExitSession::class);

        $serverTester = $this->createTester();
        $serverTester->requestAndWait('exit', []);
    }

    public function testRegistersCommands(): void
    {
        $serverTester = $this->createTester();
        $response = $serverTester->requestAndWait('workspace/executeCommand', [
            'command' => 'echo',
            'arguments' => [
                'hello',
            ],
        ]);
        $this->assertSuccess($response);
        $this->assertEquals('hello', $response->result);
    }

    public function testRegistersCodeActionProvider(): void
    {
        $serverTester = $this->createTester();
        $serverTester->textDocument()->open('file://foo', 'bar');
        $response = $serverTester->requestAndWait(CodeActionRequest::METHOD, [
            'textDocument' => [
                'uri' => 'file://foo'
            ],
            'range' => [
                'start' => [ 'line' => 0, 'character' => 0, ],
                'end' => [ 'line' => 0, 'character' => 0, ],
            ],
            'context' => [
                'diagnostics' => [],
            ],
        ]);
        $this->assertSuccess($response);
        self::assertCount(2, $response->result);
    }

    public function testNullPath(): void
    {
        $this->expectException(ExitSession::class);

        $this->createTester(InitializeParams::fromArray([
            'capabilities' => [],
            'rootUri' => null,
        ]));
    }

    public function testDisablesWorkspaceListener(): void
    {
        // workspace is enabled by default
        $container = $this->createContainer();
        self::assertInstanceOf(WorkspaceListener::class, $container->get(WorkspaceListener::class));

        // if disabled it returns NULL and will not be registered
        $container = $this->createContainer([
            LanguageServerExtension::PARAM_ENABLE_WORKPACE => false,
        ]);
        self::assertNull($container->get(WorkspaceListener::class));
    }

    public function testExceptionWhenEnablingUnknownDiagProvider(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown diagnostic');
        $serverTester = $this->createTester(null, [
            LanguageServerExtension::PARAM_DIAGNOSTIC_PROVIDERS => ['asd'],
        ]);
        $serverTester->initialize();
    }

    public function testFilterDiagnosticProviders(): void
    {
        $serverTester = $this->createTester(null, [
            LanguageServerExtension::PARAM_DIAGNOSTIC_PROVIDERS => [
                'code-action'
            ],
        ]);
        $serverTester->initialize();
    }

    public function testEnableFileEvents(): void
    {
        $params = ProtocolFactory::initializeParams($this->workspace()->path('/'));
        $params->capabilities = new ClientCapabilities([
            'didChangeWatchedFiles' => ['dynamicRegistration' => true],
        ]);
        $serverTester = $this->createTester($params, [
            LanguageServerExtension::PARAM_FILE_EVENTS => true
        ]);
        $serverTester->initialize();
        wait(delay(10));
        $message = $serverTester->transmitter()->shift();
        self::assertNotNull($message);
        assert($message instanceof RequestMessage);
        self::assertEquals('client/registerCapability', $message->method);
    }
}
