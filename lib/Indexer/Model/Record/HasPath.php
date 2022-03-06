<?php

namespace Phpactor\Indexer\Model\Record;

interface HasPath
{
    /**
     * @return $this
     */
    public function setFilePath(string $filePath);

    public function filePath(): ?string;
}
