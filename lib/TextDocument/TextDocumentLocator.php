<?php

namespace Phpactor\TextDocument;

use Phpactor\TextDocument\Exception\TextDocumentNotFound;

interface TextDocumentLocator
{
    /**
     * Retrieve text document by URI
     *
     * @throws TextDocumentNotFound
     */
    public function get(TextDocumentUri $uri): TextDocument;
}
