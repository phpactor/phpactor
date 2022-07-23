<?php

namespace Phpactor\TextDocument;

use Phpactor\TextDocument\Exception\TextDocumentNotFound;
use RuntimeException;
use Webmozart\PathUtil\Path;

final class TextDocumentBuilder
{
    private ?TextDocumentUri $uri = null;

    private ?TextDocumentLanguage $language = null;

    private string $text;

    private function __construct(string $text)
    {
        $this->text = $text;
    }

    public static function create(string $text): self
    {
        return new self($text);
    }

    public static function fromUri(string $uri, ?string $language = null): self
    {
        $uri = TextDocumentUri::fromString($uri);

        if (!file_exists($uri)) {
            throw new TextDocumentNotFound(sprintf(
                'Text Document not found at URI "%s"',
                $uri
            ));
        }

        if (!is_readable($uri)) {
            throw new RuntimeException(sprintf(
                'Could not read file at URI "%s"',
                $uri
            ));
        }

        if (null === $language) {
            $language = Path::getExtension((string) $uri);
        }

        $contents = file_get_contents($uri);

        if (false === $contents) {
            throw new RuntimeException(sprintf(
                'Could not read file at URI "%s"',
                $uri
            ));
        }

        $new = new self($contents);
        $new->uri = $uri;
        $new->language = TextDocumentLanguage::fromString($language);

        return $new;
    }

    public static function fromTextDocument(TextDocument $document): self
    {
        $new = new self($document->__toString());
        $new->uri = $document->uri();
        $new->language = $document->language();

        return $new;
    }

    public function uri(string $uri): self
    {
        $this->uri = TextDocumentUri::fromString($uri);

        return $this;
    }

    public function language(string $language): self
    {
        $this->language = TextDocumentLanguage::fromString($language);

        return $this;
    }

    public function text(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function build(): TextDocument
    {
        return new StandardTextDocument(
            $this->language ?? TextDocumentLanguage::undefined(),
            $this->text,
            $this->uri
        );
    }
}
