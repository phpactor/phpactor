<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;

class AndCriteria extends Criteria
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
            if (false === $criteria->isSatisfiedBy($record)) {
                return false;
            }
        }

        return true;
    }
}
