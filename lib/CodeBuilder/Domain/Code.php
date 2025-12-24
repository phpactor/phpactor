<?php

namespace Phpactor\CodeBuilder\Domain;

use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentLanguage;
use Phpactor\TextDocument\TextDocumentUri;

class Code implements TextDocument
{
    private function __construct(private string $code)
    {
    }

    public function __toString(): string
    {
        return $this->code;
    }

    public static function fromString(string $string): self
    {
        return new self($string);
    }

    public function uri(): ?TextDocumentUri
    {
        return null;
    }

    public function language(): TextDocumentLanguage
    {
        return TextDocumentLanguage::fromString(TextDocumentLanguage::LANGUAGE_PHP);
    }

    public function uriOrThrow(): TextDocumentUri
    {
        throw new \RuntimeException('Code builder source code does not currently have a URI');
    }
}
