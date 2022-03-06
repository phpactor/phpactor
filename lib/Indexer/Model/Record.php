<?php

namespace Phpactor\Indexer\Model;

interface Record
{
    /**
     * Return string which is unique to this record (used for namespacing),
     * e.g. "class".
     */
    public function recordType(): string;

    /**
     * Return a unique identifier for this record.
     */
    public function identifier(): string;
}
