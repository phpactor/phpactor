<?php

namespace Phpactor\Extension\LanguageServerRename\Model;

use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdits;

class LocatedTextEdits
{
    private TextEdits $textEdits;

    private TextDocumentUri $documentUri;

    private TextDocumentUri $newDocumentUri;

    public function __construct(TextEdits $textEdits, TextDocumentUri $documentUri, ?TextDocumentUri $newDocumentUri)
    {
        $this->textEdits = $textEdits;
        $this->documentUri = $documentUri;
        $this->newDocumentUri = $newDocumentUri;
    }

    public function textEdits(): TextEdits
    {
        return $this->textEdits;
    }

    public function documentUri(): TextDocumentUri
    {
        return $this->documentUri;
    }

    public function newDocumentUri(): ?TextDocumentUri
    {
        return $this->newDocumentUri;
    }
}
