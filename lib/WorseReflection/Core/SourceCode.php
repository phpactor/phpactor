<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentLanguage;
use Phpactor\TextDocument\TextDocumentUri;
use InvalidArgumentException;

class SourceCode implements TextDocument
{
    private string $source;

    /**
     * @var string
     */
    private ?string $path;

    private function __construct(string $source, string $path = null)
    {
        $this->source = $source;
        $this->path = $path;
    }

    public function __toString()
    {
        return $this->source;
    }

    public static function fromUnknown($value): SourceCode
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

        if (is_string($value)) {
            return self::fromString($value);
        }

        throw new InvalidArgumentException(sprintf(
            'Do not know how to create source code from type "%s"',
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }

    public static function fromString($source)
    {
        return new self($source);
    }

    public static function fromPath(string $filePath)
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException(sprintf(
                'File "%s" does not exist',
                $filePath
            ));
        }

        return new self(file_get_contents($filePath), $filePath);
    }

    public static function empty()
    {
        return new self('');
    }

    public static function fromPathAndString(string $filePath, string $source)
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
    
    public function language(): TextDocumentLanguage
    {
        return TextDocumentLanguage::fromString('php');
    }

    public function path()
    {
        return $this->path;
    }
}
