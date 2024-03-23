<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\HasShortName;

class ShortNameFuzzilyMatchesTo2 extends Criteria
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

        return $this->fuzzySearch($this->name, $record->shortName());
    }

    private function fuzzySearch(string $search, string $subject): bool
    {
        $index = -1;

        foreach(mb_str_split($search) as $char) {
            $index = mb_stripos($subject, $char, $index + 1);
            if ($index === false) {
                return false;
            }
        }

        return true;
    }
}
