<?php

namespace Phpactor\Indexer\Model;

use Generator;
use Phpactor\Indexer\Model\Record\FileRecord;
use Phpactor\TextDocument\Location;

interface RecordReferenceEnhancer
{
    /**
     * @return Generator<LocationConfidence>
     */
    public function enhance(string $path, ?string $containerType, string $memberName): Generator;
}
