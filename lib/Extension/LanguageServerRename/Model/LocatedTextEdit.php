<?php

namespace Phpactor\Extension\LanguageServerRename\Model;

use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdit;

final class LocatedTextEdit
{
    private TextDocumentUri $documentUri;
    private TextDocumentUri $newDocumentUri;

    private TextEdit $textEdit;

    public function __construct(TextDocumentUri $documentUri, TextEdit $textEdit, ?TextDocumentUri $newDocumentUri)
    {
        $this->documentUri = $documentUri;
        $this->newDocumentUri = $newDocumentUri;
        $this->textEdit = $textEdit;
    }

    public function textEdit(): TextEdit
    {
        return $this->textEdit;
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
