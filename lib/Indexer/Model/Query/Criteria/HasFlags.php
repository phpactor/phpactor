<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\HasFlags as RecordWithFlags;

class HasFlags extends Criteria
{
    public function __construct(private int $flag)
    {
    }

    public function isSatisfiedBy(Record $record): bool
    {
        if (!$record instanceof RecordWithFlags) {
            return false;
        }

        return $record->hasFlag($this->flag);
    }
}
