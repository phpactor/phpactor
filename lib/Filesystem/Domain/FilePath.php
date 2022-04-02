<?php

namespace Phpactor\Filesystem\Domain;

use RuntimeException;
use SplFileInfo;
use Webmozart\PathUtil\Path;
use InvalidArgumentException;

final class FilePath
{
    private $path;

    private function __construct(string $path)
    {
        if (false === Path::isAbsolute($path)) {
            throw new InvalidArgumentException(sprintf(
                'File path must be absolute, but %s given',
                $path
            ));
        }

        $this->path = $path;
    }

    public function __toString()
    {
        return $this->path;
    }

    public static function fromString(string $string): FilePath
    {
        $url = parse_url($string);

        if (false === $url) {
            throw new RuntimeException(sprintf('Cannot guess path from "%s"', $string));
        }

        $url += ['scheme' => null, 'path' => null];

        ['scheme' => $scheme, 'path' => $path] = $url;

        if (null === $path) {
            throw new RuntimeException(sprintf('No path info from URI "%s"', $string));
        }

        if (null !== $scheme && 'file' !== $scheme) {
            throw new RuntimeException(sprintf('Unsupported scheme "%s" for path "%s"', $scheme, $string));
        }

        return new self((string)$path);
    }

    public static function fromParts(array $parts): FilePath
    {
        $path = Path::join($parts);
        if (substr($path, 0, 1) !== '/') {
            $path = '/'.$path;
        }

        return new self($path);
    }

    public static function fromSplFileInfo(SplFileInfo $fileInfo): FilePath
    {
        return new self((string) $fileInfo);
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

    public function isDirectory()
    {
        return is_dir($this->path);
    }

    public function asSplFileInfo()
    {
        return new SplFileInfo($this->path());
    }

    public function makeAbsoluteFromString(string $path)
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
        return Path::getExtension($this->path);
    }

    public function concatPath(FilePath $path)
    {
        return new self(Path::join($this->path(), (string) $path));
    }

    public function isWithin(FilePath $path)
    {
        return 0 === strpos($this->path(), $path->path().'/');
    }

    public function isWithinOrSame(FilePath $path)
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

    public function path()
    {
        return $this->path;
    }
}
