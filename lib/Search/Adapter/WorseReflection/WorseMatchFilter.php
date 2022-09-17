<?php

namespace Phpactor\Search\Adapter\WorseReflection;

use Phpactor\Search\Model\Constraint\TextConstraint;
use Phpactor\Search\Model\DocumentMatches;
use Phpactor\Search\Model\MatchFilter;
use Phpactor\Search\Model\MatchToken;
use Phpactor\Search\Model\TokenConstraint;
use Phpactor\Search\Model\TokenConstraints;
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
                    fn (MatchToken $token) => $this->evalConstraint($token, $constraint)
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

    private function evalConstraint(MatchToken $token, TokenConstraint $constraint): bool
    {
        if ($constraint instanceof TextConstraint) {
            return $token->text === $constraint->text();
        }

        return false;
    }
}
