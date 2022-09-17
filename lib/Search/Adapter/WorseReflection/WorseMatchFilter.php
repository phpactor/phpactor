<?php

namespace Phpactor\Search\Adapter\WorseReflection;

use Phpactor\Search\Model\Constraint\TextConstraint;
use Phpactor\Search\Model\Constraint\TypeConstraint;
use Phpactor\Search\Model\DocumentMatches;
use Phpactor\Search\Model\MatchFilter;
use Phpactor\Search\Model\MatchToken;
use Phpactor\Search\Model\TokenConstraint;
use Phpactor\Search\Model\TokenConstraints;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;

class WorseMatchFilter implements MatchFilter
{
    private SourceCodeReflector $reflector;

    public function __construct(SourceCodeReflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function filter(DocumentMatches $matches, TokenConstraints $constraints): DocumentMatches
    {
        $filtered = [];
        foreach ($matches as $match) {
            foreach ($constraints as $constraint) {
                $tokens = $match->tokens()->filterPlaceholder(
                    $constraint->placeholder(),
                    fn (MatchToken $token) => $this->evalConstraint($matches->document(), $token, $constraint)
                );
                $match = $match->withTokens($tokens);
            }

            if ($match->hasDepletedPlaceholders()) {
                continue;
            }

            $filtered[] = $match;
        }

        return new DocumentMatches($matches->document(), $filtered);
    }

    private function evalConstraint(TextDocument $document, MatchToken $token, TokenConstraint $constraint): bool
    {
        if ($constraint instanceof TextConstraint) {
            return $token->text === $constraint->text();
        }

        if ($constraint instanceof TypeConstraint) {
            $type = $this->reflector->reflectOffset($document, $token->range->start())->symbolContext()->type();
            return $type->__toString() === $constraint->type()->__toString();
        }

        return true;
    }
}
