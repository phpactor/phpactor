<?php

namespace Phpactor\Indexer\Model\Record;

trait HasFileReferencesTrait
{
    /**
     * @var array<string,bool>
     */
    private $references = [];

    public function addReference(string $path): self
    {
        $this->references[$path] = true;

        return $this;
    }

    public function removeReference(string $path): self
    {
        if (!isset($this->references[$path])) {
            return $this;
        }

        unset($this->references[$path]);

        return $this;
    }

    /**
     * @return array<string>
     */
    public function references(): array
    {
        return array_keys($this->references);
    }
}
