<?php

namespace Phpactor\Indexer\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use RuntimeException;
use Traversable;

/**
 * @implements IteratorAggregate<IndexInfo>
 */
class IndexInfos implements IteratorAggregate, Countable
{
    /**
     * @param IndexInfo[] $infos
     */
    public function __construct(private array $infos)
    {
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->infos);
    }

    public function get(string $name): IndexInfo
    {
        foreach ($this->infos as $info) {
            if ($info->name() === $name) {
                return $info;
            }
        }

        throw new RuntimeException(sprintf(
            'Index "%s" not found. Available indices are: %s',
            $name,
            implode(', ', $this->names())
        ));
    }

    public function count(): int
    {
        return count($this->infos);
    }

    /**
     * @return string[]
     */
    public function names(): array
    {
        return array_map(function (IndexInfo $info): string {
            return $info->name();
        }, $this->infos);
    }

    /**
     * @return int[]
     */
    public function offsets(): array
    {
        return range(1, count($this->infos) + 1);
    }

    public function getByOffset(int $int): IndexInfo
    {
        $offset = 1;
        foreach ($this->infos as $info) {
            if ($offset++ === $int) {
                return $info;
            }
        }

        throw new RuntimeException(sprintf(
            'Index at offset "%s" not found. Available offsets are: %s',
            $int,
            implode(', ', $this->offsets())
        ));
    }

    public function remove(IndexInfo $target): self
    {
        return new self(array_filter($this->infos, fn (IndexInfo $info) => $info->name() !== $target->name()));
    }

    public function totalSize():int
    {
        return array_reduce(
            $this->infos,
            fn (int $size, IndexInfo $current) => $size + $current->size(),
            0
        );
    }
}
