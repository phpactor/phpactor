<?php

namespace Phpactor\Indexer\Model\SearchIndex;

use Generator;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\Record\HasFullyQualifiedName;
use Phpactor\Indexer\Model\SearchIndex;

class SearchIncludeIndex implements SearchIndex
{
    /**
     * @param list<string> $patterns
     */
    public function __construct(
        private readonly SearchIndex $innerIndex,
        private readonly array $patterns
    ) {
    }

    public function search(Criteria $criteria): Generator
    {
        foreach ($this->innerIndex->search($criteria) as $record) {
            if (!$record instanceof HasFullyQualifiedName) {
                continue;
            }
            foreach ($this->patterns as $pattern) {
                if (preg_match('{' . $pattern . '}', $record->fqn())) {
                    yield $record;
                    continue 2;
                }
            }
        }
    }

    public function write(Record $record): void
    {
        $this->innerIndex->write($record);
    }

    public function remove(Record $record): void
    {
        $this->innerIndex->remove($record);
    }

    public function flush(): void
    {
        $this->innerIndex->flush();
    }
}
