<?php

namespace Phpactor\Indexer\Model;

use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\ConstantRecord;
use Phpactor\Indexer\Model\Record\FileRecord;
use Phpactor\Indexer\Model\Record\FunctionRecord;
use Phpactor\Indexer\Model\Record\MemberRecord;
use RuntimeException;

class RecordFactory
{
    public static function create(string $type, string $identifier): Record
    {
        if ($type === ClassRecord::RECORD_TYPE) {
            return ClassRecord::fromName($identifier);
        }
        if ($type === FunctionRecord::RECORD_TYPE) {
            return FunctionRecord::fromName($identifier);
        }
        if ($type === FileRecord::RECORD_TYPE) {
            return FileRecord::fromPath($identifier);
        }
        if ($type === MemberRecord::RECORD_TYPE) {
            return MemberRecord::fromIdentifier($identifier);
        }
        if ($type === ConstantRecord::RECORD_TYPE) {
            return ConstantRecord::fromName($identifier);
        }

        throw new RuntimeException(sprintf(
            'Do not know how to create record of type "%s" with identifier "%s"',
            $type,
            $identifier
        ));
    }
}
