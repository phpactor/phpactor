<?php

namespace Phpactor\Filesystem\Domain;

use Phpactor\TextDocument\TextDocumentUri;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Filesystem\Path;

final class FilePath
{
    private function __construct(private readonly TextDocumentUri $uri)
    {
    }

    public function __toString(): string
    {
        return $this->uri->path();
    }

    public function uriAsString(): string
    {
        return $this->uri->__toString();
    }

    public static function fromString(string $string): FilePath
    {
        $textDocumentUri = TextDocumentUri::fromString($string);
        return new self($textDocumentUri);
    }

    /**
     * @param array<string> $parts
     */
    public static function fromParts(array $parts): FilePath
    {
        $path = Path::join(...$parts);
        if (!Path::isAbsolute($path)) {
            // Not sure if this makes sense… Maybe it should just throw?
            $path = Path::makeAbsolute($path, '/');
        }

        return self::fromString($path);
    }

    public static function fromSplFileInfo(SplFileInfo $fileInfo): FilePath
    {
        return self::fromString((string) $fileInfo);
    }

    public static function fromFilePathOrString(FilePath|string $path): FilePath
    {
        if ($path instanceof FilePath) {
            return $path;
        }

        if (is_string($path)) {
            return self::fromString($path);
        }
    }

    public function isDirectory(): bool
    {
        return is_dir($this->uri->path());
    }

    public function asSplFileInfo(): SplFileInfo
    {
        if ($this->uri->scheme() === 'file') {
            return new SplFileInfo($this->uri->path());
        }
        return new SplFileInfo($this->uri->__toString());
    }

    public function makeAbsoluteFromString(string $path): FilePath
    {
        if (Path::isAbsolute($path)) {
            $path = self::fromString($path);

            if (false === $path->isWithinOrSame($this)) {
                throw new RuntimeException(sprintf(
                    'Trying to create descendant from absolute path "%s" that does not lie within context path "%s"',
                    (string) $path,
                    (string) $this
                ));
            }

            return $path;
        }

        return self::fromParts([(string) $this, $path]);
    }

    public function extension(): string
    {
        return Path::getExtension($this->uri->path());
    }

    public function isWithin(FilePath $path): bool
    {
        return Path::isBasePath($path->path(), $this->path());
    }

    public function isWithinOrSame(FilePath $path): bool
    {
        if ($this->path() == $path->path()) {
            return true;
        }

        return $this->isWithin($path);
    }

    public function isNamed(string $name): bool
    {
        return basename($this->path()) == $name;
    }

    public function path(): string
    {
        return $this->uri->path();
    }
}
