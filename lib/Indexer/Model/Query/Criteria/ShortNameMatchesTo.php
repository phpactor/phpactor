<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\HasShortName;

class ShortNameMatchesTo extends Criteria
{
    public function __construct(
        private string $name,
        private bool $semiFuzzy
    ) {
    }

    public function isSatisfiedBy(Record $record): bool
    {
        if (!$record instanceof HasShortName) {
            return false;
        }

        if (!$this->name) {
            return false;
        }

        if (str_starts_with(mb_strtolower($record->shortName()), mb_strtolower($this->name))) {
            return true;
        }

        if (false === $this->semiFuzzy) {
            return false;
        }

        return $this->semiFuzzySearch($this->name, $record->shortName());
    }

    private function semiFuzzySearch(string $search, string $subject): bool
    {
        $index = -1;

        foreach (mb_str_split($search) as $char) {
            $newIndex = mb_strpos($subject, $char, $index + 1);

            if (false === $newIndex) {
                return false;
            }

            if ($newIndex === $index + 1 || ctype_upper($char) || $char === '_') {
                $index = $newIndex;
                continue;
            }

            $underscoreIndex = mb_strpos($subject, '_', $index + 1);

            if (false === $underscoreIndex || $newIndex !== $underscoreIndex + 1) {
                return false;
            }

            $index = $newIndex;
        }

        return true;
    }
}
