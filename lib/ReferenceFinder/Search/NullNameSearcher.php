<?php

namespace Phpactor\ReferenceFinder\Search;

use Generator;
use Phpactor\ReferenceFinder\NameSearcher;

class NullNameSearcher implements NameSearcher
{
    public function search(string $search): Generator
    {
        yield from [];
    }
}
