<?php

namespace Phpactor\Extension\LanguageServer\Tests\Unit\DiagnosticsProvider;

use Amp\CancellationTokenSource;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServer\CodeAction\TolerantCodeActionProvider;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Exception;

class TolerantCodeActionProviderTest extends TestCase
{
    public function testShowErrorWhenProviderFails(): void
    {
        $provider = $this->createMock(CodeActionProvider::class);
        $provider->method('provideActionsFor')->willThrowException(new Exception('Oh no!'));
        $tester = LanguageServerTesterBuilder::create();
        (new TolerantCodeActionProvider($provider, $tester->clientApi()))->provideActionsFor(
            ProtocolFactory::textDocumentItem('', ''),
            ProtocolFactory::range(1, 1, 1, 1),
            (new CancellationTokenSource())->getToken(),
        );
        $message = $tester->transmitter()->shift();
        self::assertInstanceOf(NotificationMessage::class, $message);
        $message = $message->params['message'] ?? null;
        self::assertIsString($message);
        self::assertStringContainsString('failed: Oh no!', $message);
    }
}
