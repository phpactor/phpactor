<?php

namespace Phpactor\Indexer\Model;

use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;
use SplFileInfo;
use Traversable;

/**
 * @implements IteratorAggregate<SplFileInfo>
 */
class FileList implements IteratorAggregate, Countable
{
    /**
     * Indexed by full path
     *
     * @var array<string, SplFileInfo>
     */
    private array $splFileInfos;

    /**
     * @param iterable<SplFileInfo> $splFileInfos
     */
    public function __construct(iterable $splFileInfos)
    {
        $this->splFileInfos = [];

        foreach ($splFileInfos as $splFileInfo) {
            $this->splFileInfos[$splFileInfo->getPathname()] = $splFileInfo;
        }
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @param Traversable<SplFileInfo> $splFileInfos
     */
    public static function fromInfoIterator(Traversable $splFileInfos): self
    {
        return new self($splFileInfos);
    }

    public static function fromSingleFilePath(string $subPath): self
    {
        return new self([new SplFileInfo($subPath)]);
    }

    public function merge(FileList $fileList): self
    {
        return new self(array_merge($this->splFileInfos, $fileList->splFileInfos));
    }

    /**
     * @return Iterator<SplFileInfo>
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->splFileInfos);
    }

    public function count(): int
    {
        return count($this->splFileInfos);
    }
}
