<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\ClassRecord;

class IsClassType extends Criteria
{
    private ?string $type;

    public function __construct(?string $type)
    {
        $this->type = $type;
    }

    public function isSatisfiedBy(Record $record): bool
    {
        if (!$record instanceof ClassRecord) {
            return false;
        }

        return $record->type() === $this->type;
    }
}
