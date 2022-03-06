<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\FunctionRecord;

class IsFunction extends Criteria
{
    public function isSatisfiedBy(Record $record): bool
    {
        return $record instanceof FunctionRecord;
    }
}
