<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\HasPath;

class FileAbsolutePathBeginsWith extends Criteria
{
    /**
     * @var string
     */
    private $prefix;

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    public function isSatisfiedBy(Record $record): bool
    {
        if (!$record instanceof HasPath) {
            return false;
        }

        $begins = strpos($record->filePath(), $this->prefix);
        return $begins !== false && 0 === $begins;
    }
}
