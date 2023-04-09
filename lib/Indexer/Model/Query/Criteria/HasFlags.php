<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\ClassRecord;

class HasFlags extends Criteria
{
    public function __construct(private int $flag)
    {
    }

    public function isSatisfiedBy(Record $record): bool
    {
        if (!$record instanceof ClassRecord) {
            return false;
        }

        return $record->hasFlag($this->flag);
    }
}
