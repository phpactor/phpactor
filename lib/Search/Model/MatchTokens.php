<?php

namespace Phpactor\Search\Model;

use Closure;
use Countable;
use IteratorAggregate;
use RuntimeException;
use Traversable;

/**
 * @implements IteratorAggregate<MatchToken>
 */
class MatchTokens implements Countable, IteratorAggregate
{
    /**
     * @var array<string,array<int, MatchToken>>
     */
    private array $tokens;

    /**
     * @param array<string,array<int, MatchToken>> $tokens
     */
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function count(): int
    {
        return count($this->tokens);
    }

    public function getIterator(): Traversable
    {
        foreach ($this->tokens as $placeholder => $tokens) {
            foreach ($tokens as $token) {
                yield $token;
            }
        }
    }

    public function byName(string $placeholder): self
    {
        if (!isset($this->tokens[$placeholder])) {
            throw new RuntimeException(sprintf(
                'No token exists with index/placeholder "%s"',
                $placeholder
            ));
        }

        return new self([$placeholder => $this->tokens[$placeholder]]);
    }

    public function at(int $targetOffset): MatchToken
    {
        foreach ($this->getIterator() as $offset => $token) {
            if ($targetOffset === $offset) {
                return $token;
            }
        }

        throw new RuntimeException(sprintf(
            'No tokens at offset %d',
            $targetOffset
        ));
    }

    public function filterPlaceholder(string $placeholder, Closure $predicate): self
    {
        if (!isset($this->tokens[$placeholder])) {
            throw new RuntimeException(sprintf(
                'No token exists with index/placeholder "%s"',
                $placeholder
            ));
        }
        $tokens = $this->tokens;
        $tokens[$placeholder] = array_filter($this->tokens[$placeholder], $predicate);

        return new self($tokens);
    }

    public function hasDepletedPlaceholders(): bool
    {
        foreach ($this->tokens as $placeholder => $tokens) {
            if (count($tokens) === 0) {
                return true;
            }
        }

        return false;
    }
}
