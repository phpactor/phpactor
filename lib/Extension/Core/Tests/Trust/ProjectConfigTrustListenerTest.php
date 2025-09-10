<?php

namespace Phpactor\Extension\Core\Tests\Trust;

use Phpactor\Extension\Core\Trust\Trust;
use Phpactor\Extension\LanguageServer\Listener\ProjectConfigTrustListener;
use Phpactor\LanguageServerProtocol\MessageActionItem;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\TestResponseWatcher;
use Phpactor\LanguageServer\Core\Server\RpcClient\TestRpcClient;
use Phpactor\LanguageServer\Core\Server\Transmitter\TestMessageTransmitter;
use Phpactor\Tests\IntegrationTestCase;

final class ProjectConfigTrustListenerTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testDoNotAskIfNoConfig(): void
    {
        $candidate = __DIR__ .'/phpactor.not-existing';

        $transmitter = $this->invokeListener($candidate);

        self::assertEquals(0, $transmitter->count());
    }

    public function testTrustConfig(): void
    {
        $candidate = __DIR__ .'/phpactor.foobar';
        $userResponse = ProjectConfigTrustListener::RESP_YES;

        $transmitter = $this->invokeListener($candidate, $userResponse);

        $request = $transmitter->shiftRequest();
        self::assertNotNull($request);
        self::assertEquals('window/showMessageRequest', $request->method);
        $message =$transmitter->shiftNotification();
        self::assertNotNull($message);
        self::assertEquals('window/showMessage', $message->method);

        $trust = Trust::load($this->workspace()->path('trust.json'));

        // directory should now be trusted
        self::assertTrue($trust->isTrusted(__DIR__));


        // and the user won't be bothered about it again
        $transmitter = $this->invokeListener($candidate);
        self::assertEquals(0, $transmitter->count());
    }

    public function testNoTrustConfig(): void
    {
        $candidate = __DIR__ .'/phpactor.foobar';
        $userResponse = ProjectConfigTrustListener::RESP_NO;

        $transmitter = $this->invokeListener($candidate, $userResponse);

        self::assertEquals(2, $transmitter->count());

        $trust = Trust::load($this->workspace()->path('trust.json'));

        // directory should now not be trusted
        self::assertFalse($trust->isTrusted(__DIR__));

        // and the user won't be bothered about it again
        $transmitter = $this->invokeListener($candidate);
        self::assertEquals(0, $transmitter->count());
    }

    public function testMaybe(): void
    {
        $candidate = __DIR__ .'/phpactor.foobar';
        $userResponse = ProjectConfigTrustListener::RESP_MAYBE;

        $transmitter = $this->invokeListener($candidate, $userResponse);

        self::assertEquals(1, $transmitter->count());

        $trust = Trust::load($this->workspace()->path('trust.json'));
        // directory is not trusted ...
        self::assertFalse($trust->isTrusted(__DIR__));

        // ... but we'll ask again
        $transmitter = $this->invokeListener($candidate);
        self::assertEquals(1, $transmitter->count());
    }

    private function invokeListener(string $candidate, ?string $userResponse = null): TestMessageTransmitter
    {
        $transmitter = new TestMessageTransmitter();
        $watcher = new TestResponseWatcher();
        $clientApi = new ClientApi(new TestRpcClient($transmitter, $watcher));

        $promise = (new ProjectConfigTrustListener(
            $clientApi,
            [$candidate],
            Trust::load($this->workspace()->path('trust.json'))
        ))->handleTrustConfig();

        if ($userResponse !== null) {
            $watcher->resolveLastResponse(new MessageActionItem($userResponse));
        }
        return $transmitter;
    }
}
