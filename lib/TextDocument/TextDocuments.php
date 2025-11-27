<?php

namespace Phpactor\TextDocument;

use ArrayIterator;
use IteratorAggregate;
use Phpactor\LanguageServerProtocol\TextDocument;
use Phpactor\TextDocument\Exception\TextDocumentNotFound;
use Traversable;

/**
 * @implements IteratorAggregate<TextDocument>
 */
class TextDocuments implements IteratorAggregate
{
    /**
     * @param list<TextDocument> $documents
     */
    public function __construct(private array $documents)
    {
    }

    public function fromTextDocument(TextDocument $document): self
    {
        return new self([$document]);
    }

    public function first(): TextDocument
    {
        if (!isset($this->documents[0])) {
            throw new TextDocumentNotFound(
                'Cannot get first text document as the collection is empty',
            );
        }

        return $this->documents[0];
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->documents);
    }
}
