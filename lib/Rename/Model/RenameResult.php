<?php

namespace Phpactor\Rename\Model;

use Phpactor\TextDocument\TextDocumentUri;

class RenameResult
{
    private TextDocumentUri $oldUri;

    private TextDocumentUri $newUri;

    public function __construct(TextDocumentUri $oldUri, TextDocumentUri $newUri)
    {
        $this->oldUri = $oldUri;
        $this->newUri = $newUri;
    }

    public function oldUri(): TextDocumentUri
    {
        return $this->oldUri;
    }

    public function newUri(): TextDocumentUri
    {
        return $this->newUri;
    }
}
