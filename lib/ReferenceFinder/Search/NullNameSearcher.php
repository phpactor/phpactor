<?php

namespace Phpactor\ReferenceFinder\Search;

use Generator;
use Phpactor\ReferenceFinder\NameSearcher;
use Phpactor\ReferenceFinder\NameSearcherType;

class NullNameSearcher implements NameSearcher
{
    public function search(string $search, ?NameSearcherType $type = null): Generator
    {
        yield from [];
    }
}
