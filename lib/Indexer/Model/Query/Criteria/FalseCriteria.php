<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;

class FalseCriteria extends Criteria
{
    public function isSatisfiedBy(Record $record): bool
    {
        return false;
    }
}
