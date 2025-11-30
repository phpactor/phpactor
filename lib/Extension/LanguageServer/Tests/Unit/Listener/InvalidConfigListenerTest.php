<?php

namespace Phpactor\Extension\LanguageServer\Tests\Unit\Listener;

use DTL\Invoke\Invoke;
use Phpactor\Extension\LanguageServer\Tests\Unit\LanguageServerTestCase;
use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServerProtocol\InitializeParams;

class InvalidConfigListenerTest extends LanguageServerTestCase
{
    public function testShowErrorMessageOnInvalidConfig(): Void
    {
        $tester = $this->createTester(Invoke::new(InitializeParams::class, [
            'capabilities' => new ClientCapabilities(),
            'rootUri' => 'file:///',
            'initializationOptions' => [
                'language_server.trce' => 'barfoo',
                'path' => 'barfoo',
            ]
        ]));
        $response = $tester->initialize();
        $message = $tester->transmitter()->filterByMethod('window/showMessage')->shiftNotification();
        self::assertNotNull($message);
        self::assertStringContainsString('did you mean any of', $message->params['message']);
    }
}
