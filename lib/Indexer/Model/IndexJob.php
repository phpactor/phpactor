<?php

namespace Phpactor\Indexer\Model;

use Generator;
use Phar;
use PharFileInfo;
use Phpactor\TextDocument\TextDocumentBuilder;
use RecursiveIteratorIterator;
use SplFileInfo;
use UnexpectedValueException;

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
            if ($fileInfo->isLink()) {
                continue;
            }

            // TODO: could refactor this to iterate the PHAR in the file list provider.
            if ($fileInfo->getExtension() === 'phar') {
                try {
                    $phar = new Phar($fileInfo->getPathname());
                } catch (UnexpectedValueException $e) {
                    continue;
                }
                iterator_to_array($this->indexPharFile($phar));
                yield $fileInfo->getPathname();
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
