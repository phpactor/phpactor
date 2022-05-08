<?php

namespace Phpactor\ReferenceFinder\Search;

use Generator;
use Phpactor\ReferenceFinder\NameSearcher;

class NullNameSearcher implements NameSearcher
{
    public function search(string $search, ?string $type = null): Generator
    {
        yield from [];
    }
}
