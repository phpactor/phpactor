<?php

namespace Phpactor\Search\Adapter\WorseReflection;

use ArrayIterator;
use IteratorAggregate;
use Phpactor\Search\Model\MatchToken;
use Phpactor\WorseReflection\Core\Type;
use RuntimeException;
use Traversable;

/**
 * @implements IteratorAggregate<TypedMatchToken>
 */
class TypedMatchTokens implements IteratorAggregate
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

    public function byName(string $placeholder): self
    {
        $tokens = [];
        foreach ($this->tokens as $token) {
            if ($token->name !== $placeholder) {
                continue;
            }
            $tokens[] = $token;
        }

        return new self($tokens);
    }

    public function at(int $targetOffset): MatchToken
    {
        foreach ($this->getIterator() as $offset => $token) {
            if ($targetOffset === $offset) {
                return $token;
            }
        }

        throw new RuntimeException(sprintf(
            'No tokens at offset %d', $targetOffset
        ));
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator(array_reduce($this->tokens, function (array $carry, array $tokens) {
            foreach ($tokens as $token) {
                $carry[] = $token;
            }

            return $carry;
        }, []));
    }
}
