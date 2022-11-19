<?php

namespace Phpactor\ReferenceFinder\Search;

use Generator;
use Phpactor\ReferenceFinder\NameSearcher;

class PredefinedNameSearcher implements NameSearcher
{
    /**
     * @param NameSearchResult[] $results
     */
    public function __construct(private array $results)
    {
    }

    /**
     * @return Generator<NameSearchResult>
     */
    public function search(string $search, ?string $type = null): Generator
    {
        foreach ($this->results as $result) {
            if (0 !== strpos($result->name()->head()->__toString(), $search)) {
                continue;
            }
            yield $result;
        }
    }
}
