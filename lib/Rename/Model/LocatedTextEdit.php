<?php

namespace Phpactor\Rename\Model;

use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdit;

final class LocatedTextEdit
{
    private TextDocumentUri $documentUri;

    private TextEdit $textEdit;

    public function __construct(TextDocumentUri $documentUri, TextEdit $textEdit)
    {
        $this->documentUri = $documentUri;
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
}
