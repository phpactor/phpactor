<?php

namespace Phpactor\Indexer\Model\Index;

use Phpactor\Indexer\Util\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

class IndexInfo
{
    private const SECONDS_IN_DAY = 3600 * 24;

    private string $absolutePath;

    private string $directoryName;

    private int $size;

    private float $lastUpdated;

    public function __construct(
        string $absolutePath,
        string $directoryName,
        int $size,
        float $lastUpdated
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
            Filesystem::sizeOfPath($fileInfo->getRealPath()),
            (time() - $fileInfo->getMTime()) / self::SECONDS_IN_DAY
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

    public function lastUpdatedInDays(): float
    {
        return $this->lastUpdated;
    }
}
