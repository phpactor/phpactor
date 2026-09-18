<?php

namespace Phpactor\Indexer\Model\IndexJob;

use Generator;
use Phpactor\Indexer\Model\FileList;
use Phpactor\Indexer\Model\IndexBuilder;
use Phpactor\Indexer\Model\IndexJob;

class SerialIndexJob implements IndexJob
{
    private DocumentReader $reader;

    public function __construct(
        private IndexBuilder $indexBuilder,
        private FileList $fileList,
        ?int $maxFileSizeToIndex,
    ) {
        $this->reader = new DocumentReader($maxFileSizeToIndex);
    }

    public function generator(): Generator
    {
        foreach ($this->fileList as $fileInfo) {
            $document = $this->reader->read($fileInfo);

            if (null === $document) {
                continue;
            }

            $this->indexBuilder->index($document);

            yield $fileInfo->getPathname();
        }
        $this->indexBuilder->done();
    }

    public function run(): void
    {
        iterator_to_array($this->generator());
    }

    public function size(): int
    {
        return $this->fileList->count();
    }

    public function describe(): string
    {
        return 'in this process';
    }
}
