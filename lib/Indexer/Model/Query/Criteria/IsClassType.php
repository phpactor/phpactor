<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\ClassRecord;

class IsClassType extends Criteria
{
    public function __construct(private readonly ?string $type)
    {
    }

    public function isSatisfiedBy(Record $record): bool
    {
        if (!$record instanceof ClassRecord) {
            return false;
        }

        return $record->type() === $this->type;
    }
}
