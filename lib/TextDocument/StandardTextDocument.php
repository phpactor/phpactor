<?php

namespace Phpactor\TextDocument;

use RuntimeException;

class StandardTextDocument implements TextDocument
{
    public function __construct(
        private TextDocumentLanguage $language,
        private string $text,
        private ?TextDocumentUri $uri = null
    ) {
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

    public function uriOrThrow(): TextDocumentUri
    {
        if (null === $this->uri) {
            throw new RuntimeException(
                'Document has no URI'
            );
        }
        return $this->uri;
    }
}
