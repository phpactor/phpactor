<?php

namespace Phpactor\CodeTransform\Domain;

use RuntimeException;
use Webmozart\PathUtil\Path;

final class SourceCode
{
    private string $code;

    private ?string $path;

    private function __construct(string $code, string $path = null)
    {
        $this->code = $code;

        if ($path && false === Path::isAbsolute($path)) {
            throw new RuntimeException(sprintf(
                'Path "%s" must be absolute',
                $path
            ));
        }

        $this->path = $path ? Path::canonicalize($path) : null;
    }

    public function __toString()
    {
        return $this->code;
    }

    public static function fromString(string $code): SourceCode
    {
        return new self($code);
    }

    public static function fromStringAndPath(string $code, string $path = null): SourceCode
    {
        return new self($code, $path);
    }

    public function withSource(string $code): SourceCode
    {
        return new self($code, $this->path);
    }

    public function withPath(string $path): SourceCode
    {
        return new self($this->code, $path);
    }

    public function path(): ?string
    {
        return $this->path;
    }

    public function extractSelection(int $offsetStart, int $offsetEnd): string
    {
        return substr($this->code, $offsetStart, $offsetEnd - $offsetStart);
    }

    public function replaceSelection(string $replacement, int $offsetStart, int $offsetEnd): SourceCode
    {
        $start = substr($this->code, 0, $offsetStart);
        $end = substr($this->code, $offsetEnd);

        return self::withSource($start . $replacement . $end);
    }

    /**
     * @param mixed $code
     */
    public static function fromUnknown($code): SourceCode
    {
        if ($code instanceof SourceCode) {
            return $code;
        }

        if (is_string($code)) {
            return self::fromString($code);
        }

        throw new RuntimeException(sprintf(
            'Do not know how to create source code object from "%s"',
            gettype($code)
        ));
    }
}
