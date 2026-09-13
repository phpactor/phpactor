<?php

namespace Phpactor\Indexer\Model\IndexJob;

use Generator;
use Phpactor\Indexer\Adapter\Parallel\Worker;
use Phpactor\Indexer\Model\FileList;
use Phpactor\Indexer\Model\Index;
use Phpactor\Indexer\Model\IndexBuilder;
use Phpactor\Indexer\Model\IndexJob;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\FileRecord;
use Phpactor\Indexer\Model\Record\HasFileReferences;
use Phpactor\Indexer\Model\RecordFactory;
use Phpactor\Indexer\Model\RecordMerger;
use Psr\Log\LoggerInterface;
use RuntimeException;
use SplFileInfo;
use Throwable;

final class ParallelIndexJob implements IndexJob
{
    /**
     * How long to wait between polls when every worker is busy and none of
     * them has produced anything yet.
     */
    private const IDLE_POLL_MICROSECONDS = 500;

    private DocumentReader $reader;

    /**
     * @param list<string> $command command used to spawn a worker
     */
    public function __construct(
        private Index $index,
        private IndexBuilder $builder,
        private RecordMerger $merger,
        private FileList $fileList,
        ?int $maxFileSizeToIndex,
        private array $command,
        private string $cwd,
        private int $workerCount,
        private int $chunkSize,
        private LoggerInterface $logger,
    ) {
        $this->reader = new DocumentReader($maxFileSizeToIndex);
    }

    public function generator(): Generator
    {
        $queue = array_chunk($this->paths(), max(1, $this->chunkSize));
        $workers = $this->startWorkers();

        if ([] === $workers) {
            yield from $this->indexLocally(array_merge(...$queue));
            $this->index->done();

            return;
        }

        try {
            while (true) {
                $busy = false;

                foreach ($workers as $key => $worker) {
                    if ($worker->isIdle() && [] !== $queue) {
                        $worker->assign((array) array_shift($queue));
                    }

                    // Update the process status right before draining the pipe
                    $running = $worker->isRunning();
                    $worker->poll();
                    $this->logWorkerErrors($worker);

                    foreach ($worker->takeResults() as $payload) {
                        $this->mergeRecords($payload);
                    }

                    yield from $worker->takeCompleted();

                    if (false === $running && false === $worker->isIdle()) {
                        unset($workers[$key]);
                        $abandoned = $worker->takeAbandonedChunk();
                        $this->logger->warning(sprintf(
                            'Index worker exited unexpectedly, indexing its %d remaining files in this process',
                            count($abandoned),
                        ));
                        yield from $this->indexLocally($abandoned);

                        continue;
                    }

                    $busy = $busy || false === $worker->isIdle();
                }

                if ([] === $workers) {
                    yield from $this->indexLocally(array_merge(...$queue));

                    break;
                }

                if (false === $busy && [] === $queue) {
                    break;
                }

                usleep(self::IDLE_POLL_MICROSECONDS);

                // No work to do, hand control back to the caller
                yield null;
            }
        } finally {
            foreach ($workers as $worker) {
                $worker->stop();
            }
        }

        $this->index->done();
    }

    public function run(): void
    {
        foreach ($this->generator() as $_) {
        }
    }

    public function size(): int
    {
        return $this->fileList->count();
    }

    public function describe(): string
    {
        return sprintf(
            'with %d worker processes, %d files at a time',
            $this->workerCount,
            max(1, $this->chunkSize),
        );
    }

    /**
     * @return list<Worker>
     */
    private function startWorkers(): array
    {
        $workers = [];

        for ($index = 0; $index < $this->workerCount; $index++) {
            $worker = new Worker($this->command, $this->cwd);

            try {
                $worker->start();
            } catch (Throwable $couldNotStart) {
                $this->logger->warning(sprintf(
                    'Could not start index worker, falling back to indexing in process: %s',
                    $couldNotStart->getMessage()
                ));

                foreach ($workers as $started) {
                    $started->stop();
                }

                return [];
            }

            $workers[] = $worker;
        }

        return $workers;
    }

    /**
     * @return list<string>
     */
    private function paths(): array
    {
        $paths = [];

        foreach ($this->fileList as $fileInfo) {
            $paths[] = $fileInfo->getPathname();
        }

        return $paths;
    }

    /**
     * @param list<string> $paths
     * @return Generator<string>
     */
    private function indexLocally(array $paths): Generator
    {
        foreach ($paths as $path) {
            $document = $this->reader->read(new SplFileInfo($path));

            if (null !== $document) {
                $this->builder->index($document);
            }

            yield $path;
        }
    }

    private function mergeRecords(string $payload): void
    {
        $records = unserialize($payload);

        if (!is_array($records)) {
            $this->logger->warning('Could not deserialize records from index worker');

            return;
        }

        // Rebuild the replacement for what we are retracting.
        foreach ($records as $record) {
            if ($record instanceof FileRecord) {
                $this->removeStaleReferences($record);

                continue;
            }

            if ($record instanceof ClassRecord && null !== $record->filePath()) {
                $this->removeStaleImplementations($record);
            }
        }

        foreach ($records as $record) {
            if (!$record instanceof Record) {
                continue;
            }

            $this->merger->merge($this->index, $record);
        }
    }

    private function removeStaleReferences(FileRecord $incoming): void
    {
        $existing = $this->index->get(FileRecord::fromPath($incoming->identifier()));

        foreach ($existing->references() as $reference) {
            try {
                $target = RecordFactory::create($reference->type(), $reference->identifier());
            } catch (RuntimeException) {
                continue;
            }

            $target = $this->index->get($target);

            if (!$target instanceof HasFileReferences) {
                continue;
            }

            $target->removeReference($existing->identifier());
            $this->index->write($target);
        }
    }

    /**
     * The counterpart of `AbstractClassLikeIndexer::removeImplementations()`:
     * a class which is being re-declared no longer implements whatever the
     * index thinks it does.
     */
    private function removeStaleImplementations(ClassRecord $incoming): void
    {
        $existing = $this->index->get(ClassRecord::fromName((string) $incoming->fqn()));

        foreach ($existing->implements() as $implemented) {
            $implementedRecord = $this->index->get(ClassRecord::fromName($implemented));

            if (false === $implementedRecord->removeImplementation($existing->fqn())) {
                continue;
            }

            $this->index->write($implementedRecord);
        }
    }

    private function logWorkerErrors(Worker $worker): void
    {
        $error = trim($worker->errorOutput());

        if ('' === $error) {
            return;
        }

        $this->logger->warning(sprintf('Index worker: %s', $error));
    }
}
