<?php

namespace Phpactor\TextDocument;

use ArrayIterator;
use Iterator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, TextDocumentEdits>
 */
class WorkspaceEdits implements IteratorAggregate
{
    /**
     * @var TextDocumentEdits[]
     */
    private array $documentEdits;

    public function __construct(TextDocumentEdits ...$documentEdits)
    {
        $this->documentEdits = $documentEdits;
    }
    /**
     * @return Iterator<TextDocumentEdits>
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->documentEdits);
    }
}
