<?php

namespace Phpactor\Extension\LanguageServerIndexer\Tests\Unit;

use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\Extension\LanguageServerIndexer\Tests\IntegrationTestCase;
use function Amp\Promise\wait;
use function Amp\delay;
use function Safe\json_decode;

class IndexerHandlerTest extends IntegrationTestCase
{
    private LanguageServerTester $tester;

    protected function setUp(): void
    {
        $container = $this->container([
            LanguageServerExtension::PARAM_FILE_EVENTS => false,
        ]);
        $this->tester = $container->get(LanguageServerBuilder::class)->tester(
            ProtocolFactory::initializeParams($this->workspace()->path())
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
        wait(delay(50));

        self::assertGreaterThanOrEqual(3, $this->tester->transmitter()->count());

        $progressCreationTransmission = $this->tester->transmitter()->shift();

        self::assertNotNull($token = $progressCreationTransmission->params['token']);
        self::assertArraySubset([
            'method' => 'window/workDoneProgress/create',
            'params' => [
                'token' => $token
            ]
        ], json_decode(json_encode($progressCreationTransmission), true));
        self::assertArraySubset([
            'method' => '$/progress',
            'params' => [
                'token' => $token,
                'value' => [
                    'title' => 'Indexing workspace',
                    'message' => '1 PHP files'
                ]
            ]
        ], json_decode(json_encode($this->tester->transmitter()->shift()), true));
        self::assertArraySubset([
            'method' => '$/progress',
            'params' => [
                'token' => $token
            ]
        ], json_decode(json_encode($this->tester->transmitter()->shift()), true));
        self::assertStringContainsString('Done indexing', $this->tester->transmitter()->shift()->params['message']);
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
            ProtocolFactory::initializeParams($this->workspace()->path())
        );

        $tester->initialize();
        wait(delay(10));


        $tester->transmitter()->shift();
        $tester->transmitter()->shift();
        $tester->transmitter()->shift();
        $tester->transmitter()->shift();

        $message = $tester->transmitter()->shift();
        self::assertStringContainsString('File watcher died:', $message->params['message']);
    }
}
