<?php

namespace Phpactor\Extension\LanguageServerIndexer\Handler;

use Amp\CancellationToken;
use Amp\CancelledException;
use Amp\Delayed;
use Amp\Promise;
use Generator;
use Phpactor\AmpFsWatch\Exception\WatcherDied;
use Phpactor\AmpFsWatch\Watcher;
use Phpactor\AmpFsWatch\WatcherProcess;
use Phpactor\Extension\LanguageServerIndexer\Event\IndexReset;
use Phpactor\Indexer\Model\MemoryUsage;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\Indexer\Model\Indexer;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Phpactor\LanguageServer\Core\Service\ServiceProvider;
use Phpactor\TextDocument\Exception\TextDocumentNotFound;
use Phpactor\TextDocument\TextDocumentBuilder;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

class IndexerHandler implements Handler, ServiceProvider
{
    const SERVICE_INDEXER = 'indexer';

    /**
     * @var Indexer
     */
    private $indexer;

    /**
     * @var Watcher
     */
    private $watcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ClientApi
     */
    private $clientApi;

    /**
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * @var bool
     */
    private $doReindex = false;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        Indexer $indexer,
        Watcher $watcher,
        ClientApi $clientApi,
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->indexer = $indexer;
        $this->watcher = $watcher;
        $this->logger = $logger;
        $this->clientApi = $clientApi;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return array<string>
     */
    public function methods(): array
    {
        return [
            'phpactor/indexer/reindex' => 'reindex',
        ];
    }

    /**
     * @return array<string>
     */
    public function services(): array
    {
        return [
            self::SERVICE_INDEXER
        ];
    }

    /**
     * @return Promise<mixed>
     */
    public function indexer(CancellationToken $cancel): Promise
    {
        return \Amp\call(function () use ($cancel) {
            $job = $this->indexer->getJob();
            $size = $job->size();
            $this->clientApi->window()->showMessage()->info(sprintf('Indexing "%s" PHP files', $size));

            $start = microtime(true);
            $index = 0;
            foreach ($job->generator() as $file) {
                $index++;

                if ($index % 500 === 0) {
                    $usage = MemoryUsage::create();
                    $this->clientApi->window()->showMessage()->info(sprintf(
                        'Indexed %s/%s (%s%%) %s',
                        $index,
                        $size,
                        number_format($index / $size * 100, 2),
                        $usage->memoryUsageFormatted()
                    ));
                }

                try {
                    $cancel->throwIfRequested();
                } catch (CancelledException $cancelled) {
                    break;
                }

                yield new Delayed(1);
            }

            $process = yield $this->watcher->watch();
            $this->clientApi->window()->showMessage()->info(sprintf(
                'Done indexing (%ss, %s), watching with %s',
                number_format(microtime(true) - $start, 2),
                MemoryUsage::create()->memoryUsageFormatted(),
                $this->watcher->describe()
            ));

            return yield from $this->watch($process, $cancel);
        });
    }

    public function reindex(bool $soft = false): Promise
    {
        return \Amp\call(function () use ($soft): void {
            if (false === $soft) {
                $this->indexer->reset();
            }

            $this->eventDispatcher->dispatch(new IndexReset());
        });
    }

    /**
     * @return Generator<Promise>
     */
    private function watch(WatcherProcess $process, CancellationToken $cancel): Generator
    {
        try {
            while (null !== $file = yield $process->wait()) {
                try {
                    $cancel->throwIfRequested();
                } catch (CancelledException $cancelled) {
                    break;
                }

                try {
                    $this->indexer->index(TextDocumentBuilder::fromUri($file->path())->build());
                } catch (TextDocumentNotFound $error) {
                    $this->logger->warning(sprintf(
                        'Trired to index non-existing file "%s"',
                        $file->path()
                    ));
                    continue;
                }
                $this->logger->debug(sprintf('Indexed file: %s', $file->path()));
                yield new Delayed(0);
            }
        } catch (WatcherDied $watcherDied) {
            $this->clientApi->window()->showMessage()->error(sprintf('File watcher died: %s', $watcherDied->getMessage()));
            $this->logger->error($watcherDied->getMessage());
        }
    }
}
