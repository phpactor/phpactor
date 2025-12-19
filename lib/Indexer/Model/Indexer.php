<?php

namespace Phpactor\Indexer\Model;

use Phpactor\Indexer\Model\DirtyDocumentTracker\NullDirtyDocumentTracker;
use Phpactor\TextDocument\TextDocument;

class Indexer
{
    public function __construct(
        private IndexBuilder $builder,
        private Index $index,
        private FileListProvider $provider,
        private ?int $maxFileSizeToIndex,
        private DirtyDocumentTracker $dirtyDocumentTracker = new NullDirtyDocumentTracker(),
    ) {
    }

    public function getJob(?string $subPath = null): IndexJob
    {
        return new IndexJob(
            $this->builder,
            $this->provider->provideFileList($this->index, $subPath),
            $this->maxFileSizeToIndex,
        );
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
