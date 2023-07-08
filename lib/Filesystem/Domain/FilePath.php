<?php

namespace Phpactor\Filesystem\Domain;

use Phpactor\TextDocument\TextDocumentUri;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Filesystem\Path;

final class FilePath
{
    private function __construct(private TextDocumentUri $uri)
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

    public static function fromParts(array $parts): FilePath
    {
        $path = Path::join(...$parts);
        if (!str_starts_with($path, '/')) {
            $path = '/'.$path;
        }

        return self::fromString($path);
    }

    public static function fromSplFileInfo(SplFileInfo $fileInfo): FilePath
    {
        return self::fromString((string) $fileInfo);
    }

    public static function fromUnknown($path): FilePath
    {
        if ($path instanceof FilePath) {
            return $path;
        }

        if (is_string($path)) {
            return self::fromString($path);
        }

        if (is_array($path)) {
            return self::fromParts($path);
        }

        if ($path instanceof SplFileInfo) {
            return self::fromSplFileInfo($path);
        }

        throw new RuntimeException(sprintf(
            'Do not know how to create FilePath from "%s"',
            is_object($path) ? get_class($path) : gettype($path)
        ));
    }

    public function isDirectory(): bool
    {
        return is_dir($this->uri->path());
    }

    /**
     * This will lose the scheme
     */
    public function asSplFileInfo(): SplFileInfo
    {
        return new SplFileInfo($this->uri->path());
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
        return str_starts_with($this->path(), $path->path().'/');
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
