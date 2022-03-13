<?php

namespace Phpactor\Indexer\Model\SearchClient;

use Generator;
use Phpactor\Indexer\Model\Index;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Model\SearchClient;

class HydratingSearchClient implements SearchClient
{
    private SearchClient $innerClient;

    private Index $index;

    public function __construct(Index $index, SearchClient $innerClient)
    {
        $this->innerClient = $innerClient;
        $this->index = $index;
    }

    
    public function search(Criteria $criteria): Generator
    {
        foreach ($this->innerClient->search($criteria) as $record) {
            yield $this->index->get($record);
        }
    }
}
