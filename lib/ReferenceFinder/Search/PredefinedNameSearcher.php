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
        $fullyQualified = str_starts_with($search, '\\');
        foreach ($this->results as $result) {

            if ($fullyQualified && str_starts_with('\\'. $result->name()->__toString(), $search)) {
                yield $result;
                continue;
            }
            if (str_starts_with($result->name()->head()->__toString(), $search)) {
                yield $result;
                continue;
            }
        }
    }
}
