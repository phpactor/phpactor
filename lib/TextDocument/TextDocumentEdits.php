<?php

namespace Phpactor\TextDocument;

use Iterator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, TextEdit>
 */
class TextDocumentEdits implements IteratorAggregate
{
    private TextEdits $textEdits;
    
    private TextDocumentUri $uri;


    public function __construct(TextDocumentUri $uri, TextEdits $textEdits)
    {
        $this->textEdits = $textEdits;
        $this->uri = $uri;
    }
    
    public function uri(): TextDocumentUri
    {
        return $this->uri;
    }

    public function textEdits(): TextEdits
    {
        return $this->textEdits;
    }
    /**
     * @return Iterator<TextEdit>
     */
    public function getIterator(): Iterator
    {
        return $this->textEdits->getIterator();
    }
}
