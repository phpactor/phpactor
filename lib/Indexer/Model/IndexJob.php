<?php

namespace Phpactor\Indexer\Model;

use Generator;
use Phpactor\TextDocument\TextDocumentBuilder;
use SplFileInfo;

class IndexJob
{
    /**
     * @var IndexBuilder
     */
    private $indexBuilder;

    /**
     * @var FileList
     */
    private $fileList;

    public function __construct(IndexBuilder $indexBuilder, FileList $fileList)
    {
        $this->indexBuilder = $indexBuilder;
        $this->fileList = $fileList;
    }

    /**
     * @return Generator<string>
     */
    public function generator(): Generator
    {
        foreach ($this->fileList as $fileInfo) {
            assert($fileInfo instanceof SplFileInfo);
            $contents = @file_get_contents($fileInfo->getPathname());

            if (false === $contents) {
                continue;
            }

            $this->indexBuilder->index(
                TextDocumentBuilder::create($contents)->uri($fileInfo->getPathname())->build()
            );
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
}
