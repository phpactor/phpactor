<?php

namespace Phpactor\Indexer\Model\SearchIndex;

use Generator;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\SearchIndex;

class FilteredSearchIndex implements SearchIndex
{
    /**
     * @var SearchIndex
     */
    private $innerIndex;

    /**
     * @var array<string>
     */
    private $recordTypes;


    /**
     * @param array<string> $recordTypes
     */
    public function __construct(SearchIndex $innerIndex, array $recordTypes)
    {
        $this->innerIndex = $innerIndex;
        $this->recordTypes = $recordTypes;
    }

    /**
     * {@inheritDoc}
     */
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
