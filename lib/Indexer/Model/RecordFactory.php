<?php

namespace Phpactor\Indexer\Model;

use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\ConstantRecord;
use Phpactor\Indexer\Model\Record\FileRecord;
use Phpactor\Indexer\Model\Record\FunctionRecord;
use Phpactor\Indexer\Model\Record\MemberRecord;
use Phpactor\Indexer\Model\Record\RecordType;

class RecordFactory
{
    public static function create(RecordType $type, string $identifier): Record
    {
        return match($type) {
            RecordType::CLASS_ => ClassRecord::fromName($identifier),
            RecordType::FUNCTION => FunctionRecord::fromName($identifier),
            RecordType::FILE => FileRecord::fromPath($identifier),
            RecordType::MEMBER => MemberRecord::fromIdentifier($identifier),
            RecordType::CONSTANT => ConstantRecord::fromName($identifier),
        };

    }
}
