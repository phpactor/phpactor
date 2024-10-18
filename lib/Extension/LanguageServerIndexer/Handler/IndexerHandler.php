<?php

namespace Phpactor\Extension\LanguageServerIndexer\Handler;

use function Amp\call;
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
use Phpactor\LanguageServer\Core\Service\ServiceProvider;
use Phpactor\LanguageServer\WorkDoneProgress\ProgressNotifier;
use Phpactor\LanguageServer\WorkDoneProgress\WorkDoneToken;
use Phpactor\TextDocument\Exception\TextDocumentNotFound;
use Phpactor\TextDocument\TextDocumentBuilder;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use function Amp\asyncCall;
use function Amp\delay;

class IndexerHandler implements Handler, ServiceProvider
{
    const SERVICE_INDEXER = 'indexer';

    public function __construct(
        private Indexer $indexer,
        private Watcher $watcher,
        private ClientApi $clientApi,
        private LoggerInterface $logger,
        private EventDispatcherInterface $eventDispatcher,
        private ProgressNotifier $progressNotifier,
        private ?int $reindexTimeout = null,
    ) {
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
        return call(function () use ($cancel) {
            $job = $this->indexer->getJob();
            $size = $job->size();
            $token = WorkDoneToken::generate();

            yield $this->progressNotifier->create($token);
            $this->progressNotifier->begin($token, 'Indexing workspace', sprintf('%d PHP files', $size), 0);

            $start = microtime(true);
            $index = 0;
            foreach ($job->generator() as $file) {
                $index++;

                if ($index % 500 === 0) {
                    $usage = MemoryUsage::create();
                    $this->progressNotifier->report(
                        $token,
                        sprintf(
                            '%s/%s (%s%%, %s)',
                            $index,
                            $size,
                            number_format($index / $size * 100, 2),
                            $usage->memoryUsageFormatted()
                        ),
                        (int)round($index / $size * 100)
                    );
                }

                try {
                    $cancel->throwIfRequested();
                } catch (CancelledException) {
                    break;
                }

                yield new Delayed(1);
            }

            $process = yield $this->watcher->watch();
            $message = sprintf(
                'Done indexing (%ss, %s), watching with %s',
                number_format(microtime(true) - $start, 2),
                MemoryUsage::create()->memoryUsageFormatted(),
                $this->watcher->describe()
            );
            $this->progressNotifier->end($token, $message);

            (function (?int $timeout) use ($cancel): void {
                if ($timeout === null) {
                    return;
                }
                asyncCall(function () use ($cancel, $timeout) {
                    while (true) {
                        if ($cancel->isRequested()) {
                            break;
                        }
                        yield delay($timeout);
                        yield $this->reindex(true);
                    }
                });
            })($this->reindexTimeout);

            return yield from $this->watch($process, $cancel);
        });
    }

    /**
     * @return Promise<void>
     */
    public function reindex(bool $soft = false): Promise
    {
        return call(function () use ($soft): void {
            if (false === $soft) {
                $this->indexer->reset();
            }

            $this->eventDispatcher->dispatch(new IndexReset());
        });
    }

    /**
     * @return Generator<mixed>
     */
    private function watch(WatcherProcess $process, CancellationToken $cancel): Generator
    {
        asyncCall(function () use ($process, $cancel) {
            while (true) {
                try {
                    $cancel->throwIfRequested();
                } catch (CancelledException $cancelled) {
                    $previous = $cancelled->getPrevious();
                    if ($previous) {
                        $this->logger->warning(sprintf('Watcher process cancelled: %s', $previous->getMessage()));
                    }
                    $this->logger->info('Watcher process cancelled');
                    $process->stop();
                    return;
                }
                yield new Delayed(100);
            }
        });
        try {
            while (null !== $file = yield $process->wait()) {
                try {
                    $cancel->throwIfRequested();
                } catch (CancelledException) {
                    $process->stop();
                    break;
                }

                try {
                    $this->logger->debug(sprintf('Indexing %s', $file->path()));
                    $this->indexer->index(TextDocumentBuilder::fromUri($file->path())->build());
                } catch (TextDocumentNotFound) {
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
