<?php

namespace Phpactor\Extension\LanguageServerRename\Model\Renamer;

use Generator;
use Phpactor\Extension\LanguageServerRename\Model\LocatedTextEdit;
use Phpactor\Extension\LanguageServerRename\Model\Renamer;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocument;

class InMemoryRenamer implements Renamer
{
    /**
     * @var ByteOffsetRange
     */
    private $range;

    /**
     * @var LocatedTextEdit[]
     */
    private $results;

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

    /**
     * {@inheritDoc}
     */
    public function rename(TextDocument $textDocument, ByteOffset $offset, string $newName): Generator
    {
        yield from $this->results;
    }
}
