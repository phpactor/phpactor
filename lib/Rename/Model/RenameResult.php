<?php

namespace Phpactor\Rename\Model;

use Phpactor\TextDocument\TextDocumentUri;

class RenameResult
{
    public function __construct(
        private TextDocumentUri $oldUri,
        private TextDocumentUri $newUri
    ) {
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
