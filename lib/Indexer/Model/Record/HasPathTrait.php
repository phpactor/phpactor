<?php

namespace Phpactor\Indexer\Model\Record;

trait HasPathTrait
{
    /**
     * @var string|null
     */
    protected $filePath;

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
