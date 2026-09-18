<?php

namespace Phpactor\Indexer\Model;

use Generator;
use Phpactor\Indexer\Adapter\Parallel\ParallelIndexJobFactory;
use Phpactor\Indexer\Model\DirtyDocumentTracker\NullDirtyDocumentTracker;
use Phpactor\Indexer\Model\IndexJob\SerialIndexJob;
use Phpactor\TextDocument\TextDocument;

class Indexer
{
    public function __construct(
        private IndexBuilder $builder,
        private Index $index,
        private FileListProvider $provider,
        private ?int $maxFileSizeToIndex,
        private DirtyDocumentTracker $dirtyDocumentTracker = new NullDirtyDocumentTracker(),
        private ?ParallelIndexJobFactory $parallelJobFactory = null,
    ) {
    }

    public function getJob(?string $subPath = null): IndexJob
    {
        $fileList = $this->provider->provideFileList($this->index, $subPath);

        if ($this->parallelJobFactory && $this->parallelJobFactory->supports($fileList)) {
            return $this->parallelJobFactory->create($fileList);
        }

        return new SerialIndexJob(
            $this->builder,
            $fileList,
            $this->maxFileSizeToIndex,
        );
    }
    /**
     * @return Generator<string|null>
     */
    public function optimise(bool $dryRun): Generator
    {
        yield from $this->index->optimise($dryRun);
    }

    public function index(TextDocument $textDocument): void
    {
        $this->builder->index($textDocument);
    }

    /**
     * Index a file but mark it as dirty so that it will be reloaded from disk on the next indexing run.
     */
    public function indexDirty(TextDocument $textDocument): void
    {
        if (null === $textDocument->uri()) {
            return;
        }

        $this->dirtyDocumentTracker->markDirty($textDocument->uri());
        $this->builder->index($textDocument);
    }

    public function reset(): void
    {
        $this->index->reset();
    }

    public function flush(): void
    {
        $this->index->done();
    }
}
