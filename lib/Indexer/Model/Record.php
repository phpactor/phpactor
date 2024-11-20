<?php

namespace Phpactor\Indexer\Model;

use Phpactor\Indexer\Model\Record\RecordType;

interface Record
{
    /**
     * Return string which is unique to this record (used for namespacing),
     * e.g. "class".
     */
    public function recordType(): RecordType;

    /**
     * Return a unique identifier for this record.
     */
    public function identifier(): string;
}
