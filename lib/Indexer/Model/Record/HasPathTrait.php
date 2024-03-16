<?php

namespace Phpactor\Indexer\Model\Record;

use Phpactor\TextDocument\TextDocumentUri;

trait HasPathTrait
{
    protected ?string $filePath = null;

    public function setFilePath(TextDocumentUri $uri): self
    {
        $this->filePath = $uri->__toString();
        return $this;
    }

    public function filePath(): ?string
    {
        return $this->filePath;
    }
}
