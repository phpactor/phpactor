<?php

namespace Phpactor\Extension\LanguageServerBridge\TextDocument;

use Phpactor\TextDocument\Exception\TextDocumentNotFound;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentLocator;
use Phpactor\TextDocument\TextDocumentUri;

class FilesystemWorkspaceLocator implements TextDocumentLocator
{
    public function get(TextDocumentUri $uri): TextDocument
    {
        if (!file_exists($uri->path())) {
            throw TextDocumentNotFound::fromUri($uri);
        }

        return TextDocumentBuilder::fromUri($uri->__toString())->build();
    }
}
