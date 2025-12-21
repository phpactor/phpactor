<?php

namespace Phpactor\Rename\Model\Renamer;

use Generator;
use Phpactor\Rename\Model\LocatedTextEdit;
use Phpactor\Rename\Model\Renamer;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocument;

class InMemoryRenamer implements Renamer
{
    /**
     * @param LocatedTextEdit[] $results
     */
    public function __construct(
        private ?ByteOffsetRange $range,
        private array $results = []
    ) {
    }

    public function getRenameRange(TextDocument $textDocument, ByteOffset $offset): ?ByteOffsetRange
    {
        return $this->range;
    }

    public function rename(TextDocument $textDocument, ByteOffset $offset, string $newName): Generator
    {
        yield from $this->results;
    }
}
