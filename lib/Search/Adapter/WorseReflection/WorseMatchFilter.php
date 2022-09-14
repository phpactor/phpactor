<?php

namespace Phpactor\Search\Adapter\WorseReflection;

use Phpactor\Search\Model\DocumentMatches;
use Phpactor\Search\Model\MatchFilter;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;

class WorseMatchFilter implements MatchFilter
{
    private SourceCodeReflector $reflector;

    public function __construct(SourceCodeReflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function filter(DocumentMatches $matches, string $filter): DocumentMatches
    {
        foreach ($matches as $match) {
            dump($match);
            $source = [];


            $offset = $this->reflector->reflectOffset('<?php ' . $filter, 5);
        }
            return $matches;
    }
}
