<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;

class OrCriteria extends Criteria
{
    /**
     * @var array<Criteria>
     */
    private readonly array $criterias;

    public function __construct(Criteria ...$criterias)
    {
        $this->criterias = $criterias;
    }

    public function isSatisfiedBy(Record $record): bool
    {
        foreach ($this->criterias as $criteria) {
            if (true === $criteria->isSatisfiedBy($record)) {
                return true;
            }
        }

        return false;
    }
}
