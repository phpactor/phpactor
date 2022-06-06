<?php

namespace Phpactor\Indexer\Model\RecordReferenceEnhancer;

use Generator;
use Phpactor\Indexer\Model\RecordReference;
use Phpactor\Indexer\Model\RecordReferenceEnhancer;
use Phpactor\Indexer\Model\Record\FileRecord;

class NullRecordReferenceEnhancer implements RecordReferenceEnhancer
{
    public function enhance(string $path, ?string $containerType, string $memberName): Generator
    {
    }
}
