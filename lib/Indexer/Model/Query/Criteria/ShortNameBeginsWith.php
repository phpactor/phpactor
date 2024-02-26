<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\HasShortName;

class ShortNameBeginsWith extends Criteria
{
    private string $regex;

    public function __construct(private string $name)
    {
        $this->regex = '#' . implode('.*', array_map(preg_quote(...), str_split($this->name))) . '#i';
    }

    public function isSatisfiedBy(Record $record): bool
    {
        if (!$record instanceof HasShortName) {
            return false;
        }

        if (!$this->name) {
            return false;
        }

        if (str_starts_with(strtolower($record->shortName()), $this->name)) {
            return true;
        }

        return preg_match($this->regex, $record->shortName()) === 1;
    }
}
