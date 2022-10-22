<?php

namespace Phpactor\Indexer\Model;

use Phpactor\Indexer\Util\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\SplFileInfo;

class IndexInfo
{
    private const SECONDS_IN_DAY = 3600 * 24;

    private string $absolutePath;

    private string $directoryName;

    private ?int $size;

    private float $createdAt;

    private float $updatedAt;

    public function __construct(
        string $absolutePath,
        string $directoryName,
        ?int $size,
        float $createdAt,
        float $updatedAt
    ) {
        $this->directoryName = $directoryName;
        $this->size = $size;
        $this->createdAt = $createdAt;
        $this->absolutePath = $absolutePath;
        $this->updatedAt = $updatedAt;
    }

    public static function fromSplFileInfo(SplFileInfo $fileInfo): self
    {
        return new self(
            $fileInfo->getRealPath(),
            $fileInfo->getRelativePathname(),
            null,
            $fileInfo->getCTime(),
            (function (SplFileInfo $info) {
                $path = Path::join($info->getRealPath(), 'timestamp');
                if (!file_exists($path)) {
                    return $info->getMTime();
                }
                return (int)file_get_contents($path);
            })($fileInfo)
        );
    }

    public function absolutePath(): string
    {
        return $this->absolutePath;
    }

    public function name(): string
    {
        return $this->directoryName;
    }

    public function size(): int
    {
        if ($this->size) {
            return $this->size;
        }

        $this->size = Filesystem::sizeOfPath($this->absolutePath());
        return $this->size;
    }

    public function ageInDays(): float
    {
        return (time() - $this->createdAt) / self::SECONDS_IN_DAY;
    }

    public function lastModifiedInDays(): float
    {
        return (time() - $this->updatedAt) / self::SECONDS_IN_DAY;
    }
}
