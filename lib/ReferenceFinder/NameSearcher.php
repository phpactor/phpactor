<?php

namespace Phpactor\ReferenceFinder;

use Generator;
use Phpactor\ReferenceFinder\Search\NameSearchResult;

interface NameSearcher
{
    /**
     * @return Generator<NameSearchResult>
     */
    public function search(string $search, ?NameSearcherType $type = null): Generator;
}
