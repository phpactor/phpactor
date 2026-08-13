<?php

namespace Phpactor\TextDocument;

use Phpactor\TextDocument\Exception\InvalidUriException;
use Symfony\Component\Filesystem\Path;

class TextDocumentUri
{
    public const SCHEME_FILE = 'file';
    public const SCHEME_UNTITLED = 'untitled';
    public const SCHEME_PHAR = 'phar';
    public const SCHEMES = [self::SCHEME_FILE, self::SCHEME_UNTITLED, self::SCHEME_PHAR];

    final private function __construct(
        private string $scheme,
        private string $path
    ) {
    }

    public function __toString(): string
    {
        if ($this->scheme === self::SCHEME_UNTITLED) {
            return sprintf('%s:%s', $this->scheme, $this->path);
        }
        if ($this->scheme === self::SCHEME_PHAR) {
            return sprintf('%s://%s', $this->scheme, self::_encodePath($this->path));
        }
        return sprintf('%s:///%s', $this->scheme, self::_encodePath(ltrim($this->path, '/')));
    }
    /**
     * Construct a TextDocumentUri from a URI string or a filesystem path.
     */
    public static function fromString(?string $uri): self
    {
        if ($uri === null || $uri === '') {
            throw new InvalidUriException(sprintf(
                'Could not parse_url "%s"',
                $uri
            ));
        }

        if (str_starts_with($uri, 'untitled:')) {
            return new self(self::SCHEME_UNTITLED, substr($uri, 9));
        }

        $match = preg_match('{^(?<scheme>[a-z]+://)?(?<path>.+)?}', $uri, $components, PREG_UNMATCHED_AS_NULL);
        ['scheme' => $scheme, 'path' => $path] = $components;

        if ($path === null) {
            throw new InvalidUriException(sprintf(
                'URI "%s" has no path component',
                $uri
            ));
        }

        if ($scheme === null) {
            // Allow this function to accept filesystem paths too (not URIs), convert to file: URIs

            if (!Path::isAbsolute($path)) {
                throw new InvalidUriException(sprintf(
                    'Filesystem path must be absolute, got "%s"',
                    $path
                ));
            }

            $path = Path::canonicalize($path);
            return new self(self::SCHEME_FILE, $path);
        }


        $scheme = substr($scheme, 0, -3);

        if (!in_array($scheme, self::SCHEMES)) {
            throw new InvalidUriException(sprintf(
                'Only "%s" schemes are supported, got "%s"',
                implode('", "', self::SCHEMES),
                $scheme
            ));
        }

        $path = self::_decodePath($path);

        if ($scheme === self::SCHEME_FILE && !str_starts_with($path, '/')) {
            throw new InvalidUriException(sprintf(
                'URI for file:// must be absolute, got "%s"',
                $uri
            ));
        }

        if (str_starts_with($path, '/')) {
            $path = substr($path, 1);
        }
        $path = Path::makeAbsolute($path, '/');
        return new self($scheme, $path);
    }

    public function path(): string
    {
        return $this->path;
    }

    public function scheme(): string
    {
        return $this->scheme;
    }

    /**
     * Percent encode each path segment.
     *
     * @param string $path the raw, unencoded filesystem path
     *
     * @return string the percent encoded path
     */
    private static function _encodePath(string $path): string
    {
        $encodeSegment = fn (string $segment) => strtr(rawurlencode($segment), [
            '%21' => '!', '%24' => '$', '%26' => '&', '%27' => '\'',
            '%28' => '(', '%29' => ')', '%2A' => '*', '%2B' => '+',
            '%2C' => ',', '%3A' => ':', '%3B' => ';', '%3D' => '=',
            '%40' => '@'
        ]);
        $encodedSegments = array_map($encodeSegment, explode('/', $path));
        return implode('/', $encodedSegments);
    }

    /**
     * Inverse of _encodePath(), percent decode each path segment.
     *
     * @param string $path the percent encoded path taken from a URI
     *
     * @return string unencoded filesystem path
     */
    private static function _decodePath(string $path): string
    {
        return implode('/', array_map('rawurldecode', explode('/', $path)));
    }
}
