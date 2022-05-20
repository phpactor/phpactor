<?php

namespace Phpactor\Indexer\Model\Index;

use Symfony\Component\Finder\SplFileInfo;

class IndexInfo
{
    private string $absolutePath;

    private string $directoryName;

    private int $size;

    private string $lastUpdated;

    public function __construct(
        string $absolutePath,
        string $directoryName,
        int $size,
        string $lastUpdated
    ) {
        $this->directoryName = $directoryName;
        $this->size = $size;
        $this->lastUpdated = $lastUpdated;
        $this->absolutePath = $absolutePath;
    }

    public static function create(SplFileInfo $fileInfo): self
    {
        return new self(
            $fileInfo->getRealPath(),
            $fileInfo->getRelativePathname(),
            $fileInfo->getSize(),
            sprintf('%.1f days', ((time() - $fileInfo->getMTime())/(3600*24)))
        );
    }

    public function absolutePath(): string
    {
        return $this->absolutePath;
    }

    public function directoryName(): string
    {
        return $this->directoryName;
    }

    public function size(): int
    {
        return $this->size;
    }

    public function lastUpdated(): string
    {
        return $this->lastUpdated;
    }
}
