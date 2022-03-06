<?php

namespace Phpactor\Indexer\Model\Record;

interface HasFileReferences
{
    /**
     * @return $this
     */
    public function addReference(string $path);

    /**
     * @return $this
     */
    public function removeReference(string $path);

    /**
     * @return array<string>
     */
    public function references(): array;
}
