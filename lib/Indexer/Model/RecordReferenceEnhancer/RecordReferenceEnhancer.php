<?php

namespace Phpactor\Indexer\Model\RecordReferenceEnhancer;

use Phpactor\Indexer\Model\Record\FileRecord;
use Phpactor\Indexer\Model\RecordReference;

interface RecordReferenceEnhancer
{
    /**
     * Add additional information to the record reference, e.g. determine its
     * container type through static analysis.
     */
    public function enhance(FileRecord $record, RecordReference $reference): RecordReference;
}
