<?php

namespace Phpactor\Indexer\Model\Record;

trait HasPathTrait
{
    protected ?string $filePath = null;

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function filePath(): ?string
    {
        return $this->filePath;
    }
}
