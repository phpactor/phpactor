<?php

namespace Phpactor\Search\Adapter\WorseReflection;

use Microsoft\PhpParser\Parser;
use Phpactor\Search\Model\DocumentMatches;
use Phpactor\Search\Model\MatchFilter;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use Phpactor\WorseReflection\Core\Type\BooleanType;
use RuntimeException;

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
                // only do static analysis for tokens that are placeholders.
                //
                // TODO: don't rely name being a string to decide if this is a
                //       placeholder or not.
                if (is_string($name)) {
                    $vars[$name] = new TypedMatchToken($name, $token, $this->reflector->reflectOffset(
                        $matches->document(),
                        $token->range->start()->toInt()
                    )->symbolContext()->type());
                }
            }

            $result = $this->evaluator->evaluate($expression, new TypedMatchTokens($vars));

            if (!$result instanceof BooleanType) {
                throw new RuntimeException(sprintf(
                    'Filter must evaluate to a boolean, got "%s"', $result->__toString()
                ));
            }
            if (false === $result->isTrue()) {
                continue;
            }
            $filtered[] = $match;
        }

        return new DocumentMatches($matches->document(), $filtered);
    }
}
