<?php

namespace Phpactor\Rename\Model;

use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdit;

final class LocatedTextEdit
{
    public function __construct(
        private readonly TextDocumentUri $documentUri,
        private readonly TextEdit $textEdit
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
