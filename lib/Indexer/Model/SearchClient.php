<?php

namespace Phpactor\Indexer\Model;

use Generator;
use Phpactor\Indexer\Model\Query\Criteria;

interface SearchClient
{
    /**
     * @return Generator<Record>
     */
    public function search(Criteria $criteria): Generator;
}
