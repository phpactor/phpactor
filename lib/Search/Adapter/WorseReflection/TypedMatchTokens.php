<?php

namespace Phpactor\Search\Adapter\WorseReflection;

use Phpactor\Search\Model\MatchToken;
use Phpactor\WorseReflection\Core\Type;
use RuntimeException;

class TypedMatchTokens
{
    /**
     * @var array<string,TypedMatchToken>
     */
    private array $tokens;

    /**
     * @param array<string,TypedMatchToken> $tokens
     */
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function get(string $name): TypedMatchToken
    {
        if (!isset($this->tokens[$name])) {
            throw new RuntimeException(sprintf(
                'Unknown token/placeholder "%s"', $name
            ));
        }

        return $this->tokens[$name];
    }
}
