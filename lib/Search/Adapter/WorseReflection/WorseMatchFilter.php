<?php

namespace Phpactor\Search\Adapter\WorseReflection;

use Microsoft\PhpParser\Parser;
use Phpactor\Search\Model\DocumentMatches;
use Phpactor\Search\Model\MatchFilter;
use Phpactor\Search\Model\TokenConstraints;
use Phpactor\Search\Model\TokenExprs;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use Phpactor\WorseReflection\Core\Type\BooleanType;
use RuntimeException;

class WorseMatchFilter implements MatchFilter
{
    private SourceCodeReflector $reflector;

    public function __construct(SourceCodeReflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function filter(DocumentMatches $matches, TokenConstraints $constraints): DocumentMatches
    {
        return $matches;
    }
}
