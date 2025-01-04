<?php

namespace Phpactor\Indexer\Model\Record;

use Phpactor\TextDocument\TextDocumentUri;

interface HasPath
{
    /**
     * @return $this
     */
    public function setFilePath(TextDocumentUri $filePath);

    /**
     * Rename to URI
     */
    public function filePath(): ?string;
}
