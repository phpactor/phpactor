<?php

namespace Phpactor\Indexer\Model;

interface RecordSerializer
{
    public function serialize(Record $record): string;

    public function deserialize(string $data): ?Record;
}
