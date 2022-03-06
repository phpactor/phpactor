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
                'foobar' => 'barfoo',
            ]
        ]));
        $response = $tester->initialize();
        $message = $tester->transmitter()->filterByMethod('window/showMessage')->shiftNotification();
        self::assertNotNull($message);
        self::assertStringContainsString('are not known', $message->params['message']);
    }
}
