<?php

namespace Phpactor\Indexer\Model\Record;

use RuntimeException;

trait HasPathTrait
{
    protected ?string $filePath = null;

    public function setFilePath(string $filePath): self
    {
        if (!strpos($filePath, ':/')) {
            throw new RuntimeException('Invalid file path: '.$filePath);
        }
        $this->filePath = $filePath;
        return $this;
    }

    public function filePath(): ?string
    {
        return $this->filePath;
    }
}
