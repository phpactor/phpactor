<?php

namespace Phpactor\Extension\LanguageServerRename\Model;

use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdit;

final class LocatedTextEdit
{
    /**
     * @var TextDocumentUri
     */
    private $documentUri;
    /**
     * @var TextEdit
     */
    private $textEdit;

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
