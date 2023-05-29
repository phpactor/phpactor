<?php

namespace Phpactor\TextDocument;

use Phpactor\TextDocument\Exception\InvalidUriException;
use Symfony\Component\Filesystem\Path;

class TextDocumentUri
{
    public const SCHEME_FILE = 'file';
    public const SCHEME_UNTITLED = 'untitled';
    public const SCHEMES = [self::SCHEME_FILE, self::SCHEME_UNTITLED];

    final private function __construct(private string $scheme, private string $path)
    {
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
        if (!$uri) {
            throw new InvalidUriException(sprintf(
                'Could not parse_url "%s"',
                $uri
            ));
        }

        $match = preg_match('{^(?<scheme>[a-z]+://){0,1}(?<path>.+)?}', $uri, $components);

        if (!isset($components['scheme']) || $components['scheme'] == '') {
            $components['scheme'] = self::SCHEME_FILE;
        } else {
            $components['scheme'] = substr($components['scheme'], 0, -3);
        }

        if (!isset($components['path'])) {
            throw new InvalidUriException(sprintf(
                'URI "%s" has no path component',
                $uri
            ));
        }

        if (!in_array($components['scheme'], self::SCHEMES)) {
            throw new InvalidUriException(sprintf(
                'Only "%s" schemes are supported, got "%s"',
                implode('", "', self::SCHEMES),
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
