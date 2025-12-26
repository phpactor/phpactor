<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\HasFullyQualifiedName;

class FqnBeginsWith extends Criteria
{
    public function __construct(private readonly string $name)
    {
    }

    public function isSatisfiedBy(Record $record): bool
    {
        if (!$this->name) {
            return false;
        }

        if (!$record instanceof HasFullyQualifiedName) {
            return false;
        }

        return str_starts_with($record->fqn()->__toString(), $this->name);
    }
}
