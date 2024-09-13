<?php

namespace Phpactor\Indexer\Model\RecordReferenceEnhancer;

use Phpactor\Indexer\Model\RecordReference;
use Phpactor\Indexer\Model\Record\FileRecord;

class NullRecordReferenceEnhancer implements RecordReferenceEnhancer
{
    public function enhance(FileRecord $record, RecordReference $reference): RecordReference
    {
        return $reference;
    }
}
