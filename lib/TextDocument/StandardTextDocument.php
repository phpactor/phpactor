<?php

namespace Phpactor\TextDocument;

class StandardTextDocument implements TextDocument
{
    private string $text;
    
    private ?TextDocumentUri $uri;
    
    private TextDocumentLanguage $language;

    public function __construct(
        TextDocumentLanguage $language,
        string $text,
        ?TextDocumentUri $uri = null
    ) {
        $this->text = $text;
        $this->uri = $uri;
        $this->language = $language;
    }
    
    public function __toString()
    {
        return $this->text;
    }

    
    public function uri(): ?TextDocumentUri
    {
        return $this->uri;
    }

    
    public function language(): TextDocumentLanguage
    {
        return $this->language;
    }
}
