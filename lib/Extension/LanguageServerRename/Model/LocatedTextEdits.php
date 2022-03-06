<?php

namespace Phpactor\Extension\LanguageServerRename\Model;

use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdits;

class LocatedTextEdits
{
    /**
     * @var TextEdits
     */
    private $textEdits;
    /**
     * @var TextDocumentUri
     */
    private $documentUri;

    public function __construct(TextEdits $textEdits, TextDocumentUri $documentUri)
    {
        $this->textEdits = $textEdits;
        $this->documentUri = $documentUri;
    }

    public function textEdits(): TextEdits
    {
        return $this->textEdits;
    }

    public function documentUri(): TextDocumentUri
    {
        return $this->documentUri;
    }
}
