<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\HasShortName;

class ShortNameBeginsWith extends Criteria
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function isSatisfiedBy(Record $record): bool
    {
        if (!$record instanceof HasShortName) {
            return false;
        }

        if (!$this->name) {
            return false;
        }

        return 0 === strpos($record->shortName(), $this->name);
    }
}
