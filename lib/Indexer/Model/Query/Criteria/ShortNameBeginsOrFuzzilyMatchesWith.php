<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\HasShortName;

class ShortNameBeginsOrFuzzilyMatchesWith extends Criteria
{
    private readonly string $regex;

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

        if (str_starts_with(mb_strtolower($record->shortName()), $this->name)) {
            return true;
        } 

        return preg_match($this->getRegex(), $record->shortName()) === 1;
    }

    private function getRegex(): string
    {
        $this->regex ??= '#' . implode('.*', array_map(preg_quote(...), mb_str_split($this->name))) . '#i';

        return $this->regex;
    }
}
