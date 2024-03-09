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

/**
 * @implements Iterator<array-key,FilePath>
 */
class FileList implements Iterator
{
    private int $key = 0;

    /**
     * @param Iterator<SplFileInfo> $iterator
     */
    private function __construct(private Iterator $iterator)
    {
    }


    public static function fromIterator(Iterator $iterator): self
    {
        return new self($iterator);
    }

    /**
     * @param string[] $filePaths
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
    public function getSplFileInfoIterator(): Traversable
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
        return $this->byExtensions(['php']);
    }

    /**
     * @param list<string> $extensions
     */
    public function byExtensions(array $extensions): self
    {
        return new self((function () use ($extensions) {
            foreach ($this as $filePath) {
                if (!in_array($filePath->extension(), $extensions, true)) {
                    continue;
                }

                yield $filePath->asSplFileInfo();
            }
        })());
    }

    /**
     * @param string[] $includePatterns
     * @param string[] $excludePatterns
     */
    public function includeAndExclude(array $includePatterns = [], array $excludePatterns = []): self
    {
        $inclusionMap = [];
        if ($includePatterns === []) {
            $inclusionMap['/**/*'] = true;
        }

        foreach ($includePatterns as $includePattern) {
            $inclusionMap[$includePattern] = true;
        }
        foreach ($excludePatterns as $excludePattern) {
            $inclusionMap[$excludePattern] = false;
        }

        // Sort map by keys so that more specific paths are getting matched first
        uksort($inclusionMap, function (string $x, string $y) {
            $partsX = explode(DIRECTORY_SEPARATOR, $x);
            $partsY = explode(DIRECTORY_SEPARATOR, $y);
            // Longer paths should come first
            $countDiff = -(count($partsX) <=> count($partsY));
            if ($countDiff !== 0) {
                return $countDiff;
            }

            foreach ($partsX as $i => $pathPartX) {
                if ($pathPartX === '**' || $pathPartX === '*') {
                    return 1;
                }

                $compare = strcmp($pathPartX, $partsY[$i]);
                if($compare !== 0) {
                    return $compare;
                }
            }
            return 0;
        });

        return $this->filter(function (SplFileInfo $info) use ($inclusionMap): bool {
            foreach($inclusionMap as $glob => $isIncluded) {
                if (Glob::match($info->getPathname(), $glob)) {
                    return $isIncluded;
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

    /**
     * @param Closure(SplFileInfo): bool $closure
    */
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

            return str_contains($contents, $string);
        });
    }
}
