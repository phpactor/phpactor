<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\HasShortName;

class ShortNameBeginsOrCamelMatchesTo extends Criteria
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

        if (str_starts_with(mb_strtolower($record->shortName()), $this->name)) {
            return true;
        }

        return $this->search($this->name, $record->shortName());
    }

    private function search(string $search, string $subject): bool
    {
        $index = -1;

        foreach(mb_str_split($search) as $char) {
            $newIndex = mb_strpos($subject, $char, $index + 1);
            if ($newIndex === false || !ctype_upper($char) && $newIndex !== $index + 1) {
                return false;
            }

            $index = $newIndex;
        }

        return true;
    }
}
