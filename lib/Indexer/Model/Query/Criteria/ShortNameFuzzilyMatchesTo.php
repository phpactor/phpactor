<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\HasShortName;

class ShortNameFuzzilyMatchesTo extends Criteria
{
    public function __construct(private string $name)
    {
    }

    public function isSatisfiedBy(Record $record): bool
    {
        if (!$record instanceof HasShortName) {
            return false;
        }

        if (!$this->name) {
            return false;
        }

        $regex = '#' . implode('.*', array_map(preg_quote(...), str_split($this->name))) . '#i';

        return preg_match($regex, $record->shortName()) === 1;
    }
}
