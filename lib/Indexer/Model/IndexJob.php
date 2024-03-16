<?php

namespace Phpactor\Indexer\Model;

use FilesystemIterator;
use Generator;
use Phar;
use PharFileInfo;
use Phpactor\TextDocument\TextDocumentBuilder;
use RecursiveIteratorIterator;
use SplFileInfo;

class IndexJob
{
    public function __construct(private IndexBuilder $indexBuilder, private FileList $fileList)
    {
    }

    /**
     * @return Generator<string>
     */
    public function generator(): Generator
    {

        foreach ($this->fileList as $fileInfo) {
            assert($fileInfo instanceof SplFileInfo);

            // TODO: could refactor this to iterate the PHAR in the file list provider.
            if ($fileInfo->getExtension() === 'phar') {
                $phar = new Phar($fileInfo->getPathname(), FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME);
                yield from $this->indexPharFile($phar);
                continue;
            }

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
    /**
     * @return Generator<string>
     */
    private function indexPharFile(Phar $phar): Generator
    {
        $iterator = new RecursiveIteratorIterator($phar);
        /** @var PharFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $this->indexBuilder->index(
                TextDocumentBuilder::fromUri($file->getPathname())->build()
            );
            yield $file->getPathname();
        }
    }
}
