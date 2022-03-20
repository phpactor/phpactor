<?php

namespace Phpactor\TextDocument;

use Phpactor\TextDocument\Exception\InvalidUriException;
use Webmozart\PathUtil\Path;

class TextDocumentUri
{
    private string $scheme;
    
    private string $path;

    final private function __construct(string $scheme, string $path)
    {
        $this->scheme = $scheme;
        $this->path = $path;
    }

    public function __toString(): string
    {
        return sprintf('%s://%s', $this->scheme, $this->path);
    }

    public static function fromString(string $uri): self
    {
        $components = parse_url($uri);

        if (false === $components) {
            throw new InvalidUriException(sprintf(
                'Could not parse_url "%s"',
                $uri
            ));
        }

        if (!isset($components['path'])) {
            throw new InvalidUriException(sprintf(
                'URI "%s" has no path component',
                $uri
            ));
        }

        if (isset($components['scheme']) && $components['scheme'] !== 'file') {
            throw new InvalidUriException(sprintf(
                'Only "file://" scheme is supported, got "%s"',
                $components['scheme']
            ));
        }

        if (false === Path::isAbsolute($uri)) {
            throw new InvalidUriException(sprintf(
                'URI must be absolute, got "%s"',
                $uri
            ));
        }

        return new self(
            'file',
            $components['path']
        );
    }

    public function path(): string
    {
        return $this->path;
    }

    public function scheme(): string
    {
        return $this->scheme;
    }
}
