<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\HasShortName;

class ShortNameFuzzilyMatchesTo extends Criteria
{
    private string $regex;

    public function __construct(private string $name)
    {
        $this->regex = '#' . implode('.*', array_map(preg_quote(...), mb_str_split($this->name))) . '#i';
    }

    public function isSatisfiedBy(Record $record): bool
    {
        if (!$record instanceof HasShortName) {
            return false;
        }

        if (!$this->name) {
            return false;
        }

        return preg_match($this->regex, $record->shortName()) === 1;
    }
}
