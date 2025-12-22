<?php

namespace Phpactor\Rename\Model;

use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdit;

final class LocatedTextEdit
{
    public function __construct(
        private TextDocumentUri $documentUri,
        private TextEdit $textEdit
    ) {
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
