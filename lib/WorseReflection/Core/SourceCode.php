<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentLanguage;
use Phpactor\TextDocument\TextDocumentUri;
use InvalidArgumentException;
use RuntimeException;

class SourceCode implements TextDocument
{
    private function __construct(private string $source, private ?string $path = null)
    {
    }

    public function __toString(): string
    {
        return $this->source;
    }

    public static function fromUnknown(SourceCode|TextDocument|string $value): SourceCode
    {
        if ($value instanceof SourceCode) {
            return $value;
        }

        if ($value instanceof TextDocument) {
            if (null === $value->uri()) {
                return self::fromString(
                    $value->__toString()
                );
            }
            return self::fromPathAndString(
                $value->uri()->path(),
                $value->__toString()
            );
        }

        return self::fromString($value);
    }

    public static function fromString(string $source): self
    {
        return new self($source);
    }

    public static function fromPath(string $filePath): self
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException(sprintf(
                'File "%s" does not exist',
                $filePath
            ));
        }

        return new self((string) file_get_contents($filePath), $filePath);
    }

    public static function empty(): self
    {
        return new self('');
    }

    public static function fromPathAndString(string $filePath, string $source): self
    {
        return new self($source, $filePath);
    }

    public function uri(): ?TextDocumentUri
    {
        if (!$this->path) {
            return null;
        }

        return TextDocumentUri::fromString($this->path);
    }

    public function mustGetUri(): TextDocumentUri
    {
        $uri = $this->uri();
        if (null === $uri) {
            throw new RuntimeException(
                'URI has not been set on source code'
            );
        }

        return $uri;
    }

    public function language(): TextDocumentLanguage
    {
        return TextDocumentLanguage::fromString('php');
    }

    public function path(): ?string
    {
        return $this->path;
    }
}
