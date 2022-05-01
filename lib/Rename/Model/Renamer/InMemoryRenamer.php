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
    private ?ByteOffsetRange $range;

    /**
     * @var LocatedTextEdit[]
     */
    private array $results;

    /**
     * @param LocatedTextEdit[] $results
     */
    public function __construct(?ByteOffsetRange $range, array $results = [])
    {
        $this->results = $results;
        $this->range = $range;
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
