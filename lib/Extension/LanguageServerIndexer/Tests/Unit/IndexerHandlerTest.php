<?php

namespace Phpactor\Extension\LanguageServerIndexer\Tests\Unit;

use Phly\EventDispatcher\EventDispatcher;
use Phpactor\AmpFsWatch\Watcher;
use Phpactor\Container\Container;
use Phpactor\Extension\LanguageServerIndexer\Handler\IndexerHandler;
use Phpactor\Extension\LanguageServerIndexer\Listener\IndexerListener;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Phpactor\Indexer\Model\Indexer;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\Core\Server\ResponseWatcher\TestResponseWatcher;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\Extension\LanguageServerIndexer\Tests\IntegrationTestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\NullLogger;
use function Amp\Promise\wait;
use function Amp\delay;

class IndexerHandlerTest extends IntegrationTestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = $this->container([
            LanguageServerExtension::PARAM_FILE_EVENTS => false,
        ]);
    }

    public function testIndexer(): void
    {
        [$tester, $watcher] = $this->createTester($this->container);
        $this->workspace()->put(
            'Foobar.php',
            <<<'EOT'
                <?php
                EOT
        );

        $tester->initialize();
        $watcher->resolveLastResponse(null);
        wait(delay(50));

        self::assertGreaterThanOrEqual(2, $tester->transmitter()->count());
        self::assertStringContainsString('window/workDoneProgress/create', $tester->transmitter()->shiftRequest()->method);
        $tester->transmitter()->shift();
        self::assertStringContainsString('Done indexing', $tester->transmitter()->shift()->params['value']['message']);
    }

    public function testReindexNonStarted(): void
    {
        [$tester, $watcher] = $this->createTester($this->container);
        $tester->initialize();

        wait(delay(10));

        self::assertContains('indexer', $tester->services()->listRunning());
        $tester->services()->stop('indexer');
        self::assertNotContains('indexer', $tester->services()->listRunning());

        $tester->notifyAndWait('phpactor/indexer/reindex', []);

        self::assertContains('indexer', $tester->services()->listRunning());
    }

    public function testReindexHard(): void
    {
        [$tester, $watcher] = $this->createTester($this->container);
        $tester->notifyAndWait('phpactor/indexer/reindex', [
            'soft' => false,
        ]);

        self::assertContains('indexer', $tester->services()->listRunning());
    }

    public function testShowsMessageOnWatcherDied(): void
    {
        $this->workspace()->put(
            'Foobar.php',
            <<<'EOT'
                <?php
                EOT
        );

        $container = $this->container([
            'indexer.enabled_watchers' => ['will_die'],
        ]);
        [$tester, $watcher] = $this->createTester($container);

        $tester->initialize();
        $watcher->resolveLastResponse(null);
        wait(delay(10));


        $tester->transmitter()->shift();
        $tester->transmitter()->shift();
        $tester->transmitter()->shift();

        $message = $tester->transmitter()->shift();
        self::assertStringContainsString('File watcher died:', $message->params['message']);
    }

    /**
     * @return array{LanguageServerTester, TestResponseWatcher}
     */
    private function createTester(Container $container): array
    {
        $builder = LanguageServerTesterBuilder::create();
        $handler = new IndexerHandler(
            $container->get(Indexer::class),
            $container->get(Watcher::class),
            $builder->clientApi(),
            new NullLogger(),
            $container->get(EventDispatcherInterface::class)
        );
        $builder->addServiceProvider($handler);
        $builder->addHandler($handler);
        $watcher = $builder->responseWatcher();
        $tester = $builder->build();
        return [$tester, $watcher];
    }
}
