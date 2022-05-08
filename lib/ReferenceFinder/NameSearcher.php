<?php

namespace Phpactor\ReferenceFinder;

use Generator;
use Phpactor\ReferenceFinder\Search\NameSearchResult;

interface NameSearcher
{
    /**
     * @return Generator<NameSearchResult>
     * @param NameSearcherType::* $type
     */
    public function search(string $search, ?string $type = null): Generator;
}
