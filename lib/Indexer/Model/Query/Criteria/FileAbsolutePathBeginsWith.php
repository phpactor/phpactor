<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\HasPath;
use function str_starts_with;

class FileAbsolutePathBeginsWith extends Criteria
{
    public function __construct(private string $prefix)
    {
    }

    public function isSatisfiedBy(Record $record): bool
    {
        if (!$record instanceof HasPath) {
            return false;
        }

        return str_starts_with($record->filePath() ?? '', $this->prefix);
    }
}
