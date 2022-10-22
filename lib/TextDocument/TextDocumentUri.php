<?php

namespace Phpactor\TextDocument;

use Phpactor\TextDocument\Exception\InvalidUriException;
use Symfony\Component\Filesystem\Path;

class TextDocumentUri
{
    public const SCHEME_FILE = 'file';
    public const SCHEME_UNTITLED = 'untitled';

    private string $scheme;

    private string $path;

    final private function __construct(string $scheme, string $path)
    {
        $this->scheme = $scheme;
        $this->path = $path;
    }

    public function __toString(): string
    {
        if ($this->scheme === self::SCHEME_FILE) {
            return sprintf('%s://%s', $this->scheme, $this->path);
        }
        return sprintf('%s:%s', $this->scheme, $this->path);
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

        $components['scheme'] = $components['scheme'] ?? self::SCHEME_FILE;

        if (!in_array($components['scheme'], [self::SCHEME_FILE, self::SCHEME_UNTITLED])) {
            throw new InvalidUriException(sprintf(
                'Only "file://" scheme is supported, got "%s"',
                $components['scheme']
            ));
        }

        if ($components['scheme'] === self::SCHEME_FILE && false === Path::isAbsolute($uri)) {
            throw new InvalidUriException(sprintf(
                'URI for file:// must be absolute, got "%s"',
                $uri
            ));
        }

        $components['path'] = Path::canonicalize($components['path']);

        return new self(
            $components['scheme'],
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
