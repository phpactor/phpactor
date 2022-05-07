<?php

namespace Phpactor\ReferenceFinder\Search;

use Generator;
use Phpactor\ReferenceFinder\NameSearcher;

class PredefinedNameSearcherASD implements NameSearcher
{
    /**
     * @var NameSearchResult[]
     */
    private array $results;

    /**
     * @param NameSearchResult[] $results
     */
    public function __construct(array $results)
    {
        $this->results = $results;
    }

    /**
     * @return Generator<NameSearchResult>
     */
    public function search(string $search): Generator
    {
        foreach ($this->results as $result) {
            if (0 !== strpos($result->name()->head()->__toString(), $search)) {
                continue;
            }
            yield $result;
        }
    }
}
