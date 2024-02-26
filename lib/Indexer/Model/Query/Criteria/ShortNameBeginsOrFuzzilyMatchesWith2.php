<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;

class ShortNameBeginsOrFuzzilyMatchesWith2 extends Criteria
{
    private ShortNameBeginsWith $beginsWith;
    private ShortNameFuzzilyMatchesTo $fuzzilyMatches;

    public function __construct(string $name)
    {
        $this->beginsWith = new ShortNameBeginsWith($name);
        $this->fuzzilyMatches = new ShortNameFuzzilyMatchesTo($name);
    } 

    public function isSatisfiedBy(Record $record): bool
    {
        return $this->beginsWith->isSatisfiedBy($record) || $this->fuzzilyMatches->isSatisfiedBy($record);
    }
}
