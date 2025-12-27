<?php

namespace Phpactor\Indexer\Model\SearchClient;

use Generator;
use Phpactor\Indexer\Model\Index;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\SearchClient;

class HydratingSearchClient implements SearchClient
{
    public function __construct(
        private readonly Index $index,
        private readonly SearchClient $innerClient
    ) {
    }


    public function search(Criteria $criteria): Generator
    {
        foreach ($this->innerClient->search($criteria) as $record) {
            yield $this->index->get($record);
        }
    }
}
