<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\ConstantRecord;

class IsConstant extends Criteria
{
    public function isSatisfiedBy(Record $record): bool
    {
        return $record instanceof ConstantRecord;
    }
}
