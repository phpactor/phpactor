<?php

namespace Phpactor\Indexer\Model\Query\Criteria;

use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\HasPath;
use function str_starts_with;

class FileAbsolutePathBeginsWith extends Criteria
{
    public function __construct(private readonly string $prefix)
    {
    }

    public function isSatisfiedBy(Record $record): bool
    {
        if (!$record instanceof HasPath) {
            return false;
        }
        $path = $record->filePath();
        if (!$path) {
            return false;
        }
        if ($pos = strpos($path, ':///')) {
            $path = substr($path, $pos + 3);
        }

        return str_starts_with($path, $this->prefix);
    }
}
