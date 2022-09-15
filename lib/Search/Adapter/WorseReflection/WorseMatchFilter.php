<?php

namespace Phpactor\Search\Adapter\WorseReflection;

use Microsoft\PhpParser\Parser;
use Phpactor\Search\Model\DocumentMatches;
use Phpactor\Search\Model\MatchFilter;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;

class WorseMatchFilter implements MatchFilter
{
    private SourceCodeReflector $reflector;
    private Parser $parser;
    private WorseFilterEvaluator $evaluator;

    public function __construct(SourceCodeReflector $reflector, Parser $parser, WorseFilterEvaluator $evaluator)
    {
        $this->reflector = $reflector;
        $this->parser = $parser;
        $this->evaluator = $evaluator;
    }

    public function filter(DocumentMatches $matches, string $filter): DocumentMatches
    {
        $filtered = [];
        $expression  = $this->parser->parseSourceFile('<?php ' . $filter . ';');
        foreach ($matches as $match) {
            $vars = [];
            foreach ($match->tokens() as $name => $token) {
                $vars[$name] = new TypedMatchToken($name, $token, $this->reflector->reflectOffset(
                    $matches->document(),
                    $token->range->start()->toInt()
                )->symbolContext()->type());
            }

            if ($this->evaluator->evaluate($expression, $vars)->isFalse()) {
                continue;
            }
            $filtered[] = $match;
        }

        return new DocumentMatches($matches->document(), $filtered);
    }
}
