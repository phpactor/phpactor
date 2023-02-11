<?php

namespace Phpactor\Extension\LanguageServerIndexer\Tests\Unit;

use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\LanguageServerProtocol\ClientCapabilities;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServerProtocol\WindowClientCapabilities;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\Extension\LanguageServerIndexer\Tests\IntegrationTestCase;
use function Amp\Promise\wait;
use function Amp\delay;

class IndexerHandlerTest extends IntegrationTestCase
{
    private LanguageServerTester $tester;

    protected function setUp(): void
    {
        $container = $this->container([
            LanguageServerExtension::PARAM_FILE_EVENTS => false,
        ]);
        $this->tester = $container->get(LanguageServerBuilder::class)->tester(
            new InitializeParams(
                rootUri: $this->workspace()->path(),
                capabilities: new ClientCapabilities(window: new WindowClientCapabilities(workDoneProgress: true))
            )
        );
    }

    public function testIndexer(): void
    {
        $this->workspace()->put(
            'Foobar.php',
            <<<'EOT'
                <?php
                EOT
        );

        $this->tester->initialize();
        $response = $this->tester->transmitter()->shiftRequest();
        $this->tester->respond($response->id, null);
        wait(delay(50));

        self::assertGreaterThanOrEqual(2, $this->tester->transmitter()->count());
        $this->tester->transmitter()->shift();
        $done = $this->tester->transmitter()->shift();
        self::assertStringContainsString('Done indexing', $done->params['value']['message']);
    }

    public function testReindexNonStarted(): void
    {
        $this->tester->initialize();

        wait(delay(10));

        self::assertContains('indexer', $this->tester->services()->listRunning());
        $this->tester->services()->stop('indexer');
        self::assertNotContains('indexer', $this->tester->services()->listRunning());

        $this->tester->notifyAndWait('phpactor/indexer/reindex', []);

        self::assertContains('indexer', $this->tester->services()->listRunning());
    }

    public function testReindexHard(): void
    {
        $this->tester->notifyAndWait('phpactor/indexer/reindex', [
            'soft' => false,
        ]);

        self::assertContains('indexer', $this->tester->services()->listRunning());
    }

    public function testShowsMessageOnWatcherDied(): void
    {
        $this->workspace()->put(
            'Foobar.php',
            <<<'EOT'
                <?php
                EOT
        );

        $tester = $this->container([
            'indexer.enabled_watchers' => ['will_die'],
        ])->get(LanguageServerBuilder::class)->tester(
            new InitializeParams(
                rootUri: $this->workspace()->path(),
                capabilities: new ClientCapabilities(window: new WindowClientCapabilities(workDoneProgress: true))
            )
        );

        $tester->initialize();
        $response = $tester->transmitter()->shiftRequest();
        $tester->respondAndWait($response->id, null);
        wait(delay(10));

        $tester->transmitter()->shift();
        $tester->transmitter()->shift();
        $message = $tester->transmitter()->shift();
        self::assertStringContainsString('File watcher died:', $message->params['message']);
    }
}
