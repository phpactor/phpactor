<?php

namespace Phpactor\Indexer\Model;

use Phpactor\Indexer\Model\Record\FileRecord;

interface RecordReferenceEnhancer
{
    /**
     * Add additional information to the record reference, e.g. determine its
     * container type through static analysis.
     */
    public function enhance(FileRecord $record, RecordReference $reference): RecordReference;
}
