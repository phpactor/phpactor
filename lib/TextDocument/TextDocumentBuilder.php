<?php

namespace Phpactor\TextDocument;

use Phpactor\TextDocument\Exception\TextDocumentNotFound;
use RuntimeException;
use Symfony\Component\Filesystem\Path;

final class TextDocumentBuilder
{
    private ?TextDocumentUri $uri = null;

    private ?TextDocumentLanguage $language = null;

    private function __construct(private string $text)
    {
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

    /**
     * @deprecated this method encourages the creation of documents without the URI.
     */
    public static function fromUnknown(TextDocument|string $sourceCode): TextDocument
    {
        if ($sourceCode instanceof TextDocument) {
            return $sourceCode;
        }

        return self::create($sourceCode)->build();
    }

    public static function empty(): TextDocument
    {
        return self::create('')->build();
    }

    public static function fromPathAndString(string $path, string $string): TextDocument
    {
        return self::create($string)->uri($path)->build();
    }

    public static function fromString(string $string): TextDocument
    {
        return self::create($string)->build();
    }
}
