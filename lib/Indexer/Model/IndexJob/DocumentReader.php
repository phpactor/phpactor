<?php

namespace Phpactor\Indexer\Model\IndexJob;

use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use SplFileInfo;

/**
 * Decides whether a file should be indexed at all, and reads it if so.
 */
final class DocumentReader
{
    public function __construct(private ?int $maxFileSizeToIndex)
    {
    }

    public function read(SplFileInfo $fileInfo): ?TextDocument
    {
        if ($fileInfo->isLink()) {
            return null;
        }

        if (($fileInfo->getSize() ?: 0) >= $this->maxFileSizeToIndex) {
            return null;
        }

        $contents = @file_get_contents($fileInfo->getPathname());

        if (false === $contents) {
            return null;
        }

        return TextDocumentBuilder::create($contents)->uri($fileInfo->getPathname())->build();
    }
}
