<?php

namespace Phpactor\Indexer\Model\SearchIndex;

use Generator;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\SearchIndex;

/**
 * Only writes the given record types to the underlying index.
 */
class FilteredSearchIndex implements SearchIndex
{
    /**
     * @param array<string> $recordTypes
     */
    public function __construct(
        private SearchIndex $innerIndex,
        private array $recordTypes
    ) {
    }


    public function search(Criteria $criteria): Generator
    {
        return $this->innerIndex->search($criteria);
    }

    public function write(Record $record): void
    {
        if (!in_array($record->recordType(), $this->recordTypes)) {
            return;
        }

        $this->innerIndex->write($record);
    }

    public function flush(): void
    {
        $this->innerIndex->flush();
    }

    public function remove(Record $record): void
    {
        $this->innerIndex->remove($record);
    }
}
