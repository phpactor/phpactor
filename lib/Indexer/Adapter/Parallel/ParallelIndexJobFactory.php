<?php

namespace Phpactor\Indexer\Adapter\Parallel;

use Phpactor\Indexer\Model\FileList;
use Phpactor\Indexer\Model\Index;
use Phpactor\Indexer\Model\IndexBuilder;
use Phpactor\Indexer\Model\IndexJob\ParallelIndexJob;
use Phpactor\Indexer\Model\RecordMerger;
use Psr\Log\LoggerInterface;

final class ParallelIndexJobFactory
{
    /**
     * Aim for this many chunks per worker so that the pool stays evenly loaded
     * when some files turn out to be much more expensive than others.
     */
    private const CHUNKS_PER_WORKER = 8;
    private const MIN_CHUNK_SIZE = 10;
    private const MAX_CHUNK_SIZE = 250;

    /**
     * @param list<string> $command command used to spawn a worker
     */
    public function __construct(
        private Index $index,
        private IndexBuilder $builder,
        private array $command,
        private string $cwd,
        private int $workerCount,
        private int $minimumFiles,
        private ?int $maxFileSizeToIndex,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Spawning a pool is only worth it for a substantial batch of files.
     */
    public function supports(FileList $fileList): bool
    {
        return $this->workerCount > 1 && $fileList->count() >= $this->minimumFiles;
    }

    public function create(FileList $fileList): ParallelIndexJob
    {
        return new ParallelIndexJob(
            $this->index,
            $this->builder,
            new RecordMerger(),
            $fileList,
            $this->maxFileSizeToIndex,
            $this->command,
            $this->cwd,
            $this->workerCount,
            $this->chunkSize($fileList->count()),
            $this->logger,
        );
    }

    private function chunkSize(int $fileCount): int
    {
        return max(self::MIN_CHUNK_SIZE, min(
            self::MAX_CHUNK_SIZE,
            (int) ceil($fileCount / ($this->workerCount * self::CHUNKS_PER_WORKER))
        ));
    }
}
