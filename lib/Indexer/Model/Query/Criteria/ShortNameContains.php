<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\HasShortName;

class ShortNameContains extends Criteria
{
    /**
     * @var string
     */
    private $substr;

    public function __construct(string $substr)
    {
        $this->substr = $substr;
    }

    public function isSatisfiedBy(Record $record): bool
    {
        if (!$record instanceof HasShortName) {
            return false;
        }

        return false !== stripos($record->shortName(), $this->substr);
    }
}
