<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;

class TrueCriteria extends Criteria
{
    public function isSatisfiedBy(Record $record): bool
    {
        return true;
    }
}
