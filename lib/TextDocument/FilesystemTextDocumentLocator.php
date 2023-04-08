<?php

namespace Phpactor\TextDocument;

use Phpactor\TextDocument\Exception\TextDocumentNotFound;

class FilesystemTextDocumentLocator implements TextDocumentLocator
{
    public function get(TextDocumentUri $uri): TextDocument
    {
        if (!file_exists($uri->path())) {
            throw TextDocumentNotFound::fromUri($uri);
        }

        return TextDocumentBuilder::fromUri($uri->__toString())->build();
    }
}
