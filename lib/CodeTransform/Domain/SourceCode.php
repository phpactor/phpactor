<?php

namespace Phpactor\CodeTransform\Domain;

use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentLanguage;
use Phpactor\TextDocument\TextDocumentUri;
use RuntimeException;

final class SourceCode implements TextDocument
{
    private function __construct(private string $code, private TextDocumentUri $path)
    {
    }

    public function __toString(): string
    {
        return $this->code;
    }

    public static function fromString(string $code): SourceCode
    {
        return new self($code, TextDocumentUri::fromString('untitled:Untitled'));
    }

    public static function fromStringAndPath(string $code, string $path = null): SourceCode
    {
        return new self($code, TextDocumentUri::fromString($path));
    }

    public function withSource(string $code): SourceCode
    {
        return new self($code, $this->path);
    }

    public function withPath(string $path): SourceCode
    {
        return new self($this->code, TextDocumentUri::fromString($path));
    }

    public function path(): string
    {
        return $this->path->path();
    }

    public function extractSelection(int $offsetStart, int $offsetEnd): string
    {
        return substr($this->code, $offsetStart, $offsetEnd - $offsetStart);
    }

    public function replaceSelection(string $replacement, int $offsetStart, int $offsetEnd): SourceCode
    {
        $start = substr($this->code, 0, $offsetStart);
        $end = substr($this->code, $offsetEnd);

        return self::withSource($start . $replacement . $end);
    }

    /**
     * @param mixed $code
     */
    public static function fromUnknown($code): SourceCode
    {
        if ($code instanceof SourceCode) {
            return $code;
        }

        if (is_string($code)) {
            return self::fromString($code);
        }

        throw new RuntimeException(sprintf(
            'Do not know how to create source code object from "%s"',
            gettype($code)
        ));
    }

    public function uri(): TextDocumentUri
    {
        return $this->path;
    }

    public function language(): TextDocumentLanguage
    {
        return TextDocumentLanguage::fromString('php');
    }

    /**
     * Create a SourceCode class from the standard TextDocument. In the long
     * term this package should be updated to work with this TextDocument
     * interface and not depend on it's own representation.
     */
    public static function fromTextDocument(TextDocument $textDocument): self
    {
        return new self($textDocument->__toString(), $textDocument->uri());
    }
}
