<?php

namespace Phpactor\Filesystem\Domain;

use CallbackFilterIterator;
use Iterator;
use RegexIterator;
use ReturnTypeWillChange;
use SplFileInfo;
use Traversable;
use Webmozart\Glob\Glob;
use ArrayIterator;
use Closure;

class FileList implements Iterator
{
    private $iterator;

    private $key = 0;

    private function __construct(Iterator $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * @return FileList<SplFileInfo>
     */
    public static function fromIterator(Iterator $iterator): self
    {
        return new self($iterator);
    }

    /**
     * @return FileList<SplFileInfo>
     */
    public static function fromFilePaths(array $filePaths): self
    {
        $files = [];
        foreach ($filePaths as $filePath) {
            $files[] = new SplFileInfo($filePath);
        }

        return new self(new ArrayIterator($files));
    }

    /**
     * @return Iterator<SplFileInfo>
     */
    public function getIterator(): Traversable
    {
        return $this->iterator;
    }

    public function contains(FilePath $path): bool
    {
        foreach ($this as $filePath) {
            if ($path == $filePath) {
                return true;
            }
        }

        return false;
    }

    public function phpFiles(): self
    {
        return new self((function () {
            foreach ($this as $filePath) {
                if ($filePath->extension() !== 'php') {
                    continue;
                }

                yield $filePath->asSplFileInfo();
            }
        })());
    }

    public function excludePatterns(array $globPatterns): self
    {
        return $this->filter(function (SplFileInfo $info) use ($globPatterns) {
            foreach ($globPatterns as $pattern) {
                if (Glob::match($info->getPathname(), $pattern)) {
                    return false;
                }
            }

            return true;
        });
    }

    public function includePatterns(array $globPatterns): self
    {
        return $this->filter(function (SplFileInfo $info) use ($globPatterns) {
            foreach ($globPatterns as $pattern) {
                if (Glob::match($info->getPathname(), $pattern)) {
                    return true;
                }
            }

            return false;
        });
    }

    public function within(FilePath $path): self
    {
        return new self(new RegexIterator($this->iterator, sprintf(
            '{^%s/.*}',
            (string) preg_quote($path)
        )));
    }

    public function named(string $name): self
    {
        return new self(new RegexIterator($this->iterator, sprintf(
            '{/%s$}',
            preg_quote($name)
        )));
    }

    public function filter(Closure $closure): self
    {
        return new self(new CallbackFilterIterator($this->iterator, $closure));
    }

    public function existing(): self
    {
        return new self(new CallbackFilterIterator($this->iterator, function (SplFileInfo $file) {
            return file_exists($file->__toString());
        }));
    }

    public function rewind(): void
    {
        $this->iterator->rewind();
    }

    #[ReturnTypeWillChange]
    public function current()
    {
        $current = $this->iterator->current();

        return FilePath::fromSplFileInfo($current);
    }

    #[ReturnTypeWillChange]
    public function key()
    {
        return $this->key++;
    }

    public function next(): void
    {
        $this->iterator->next();
    }

    public function valid(): bool
    {
        return $this->iterator->valid();
    }

    /**
     * @return self<FilePath>
     */
    public function containingString(string $string): self
    {
        return $this->filter(function (SplFileInfo $info) use ($string) {
            $contents = @file_get_contents($info->getPathname());

            if (false === $contents) {
                return false;
            }

            return false !== strpos($contents, $string);
        });
    }
}
