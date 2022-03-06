<?php

namespace Phpactor\Indexer\Model\RecordSerializer;

use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\RecordSerializer;

class PhpSerializer implements RecordSerializer
{
    public function serialize(Record $record): string
    {
        return serialize($record);
    }

    public function deserialize(string $data): ?Record
    {
        return unserialize($data);
    }
}
