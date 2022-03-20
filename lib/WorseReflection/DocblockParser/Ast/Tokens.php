<?php

namespace Phpactor\WorseReflection\DocblockParser\Ast;

use ArrayIterator;
use IteratorAggregate;
use RuntimeException;

/**
 * @implements IteratorAggregate<int, Token>
 */
final class Tokens implements IteratorAggregate
{
    /**
     * @var ?Token
     */
    public $current;

    /**
     * @var Token[]
     */
    private array $tokens;
    
    private int $position = 0;

    /**
     * @param Token[] $tokens
     */
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
        if (count($tokens)) {
            $this->current = $tokens[$this->position];
        }
    }

    /**
     * @return Token[]
     */
    public function toArray(): array
    {
        return $this->tokens;
    }

    /**
     * @return ArrayIterator<int,Token>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->tokens);
    }

    public function hasCurrent(): bool
    {
        return isset($this->tokens[$this->position]);
    }

    public function hasAnother(): bool
    {
        return isset($this->tokens[$this->position + 1]);
    }

    /**
     * Return the current token and move the position ahead.
     */
    public function chomp(?string $type = null): ?Token
    {
        if (!isset($this->tokens[$this->position])) {
            return null;
        }

        $token = $this->tokens[$this->position++];
        $this->current = @$this->tokens[$this->position];

        if (null !== $type && $token->type !== $type) {
            throw new RuntimeException(sprintf(
                'Expected type "%s" at position "%s": "%s"',
                $type,
                $this->position,
                implode('', array_map(function (Token $token) {
                    return $token->value;
                }, $this->tokens))
            ));
        }

        return $token;
    }

    /**
     * Chomp only if the current node is the given type
     */
    public function chompIf(string $type): ?Token
    {
        if ($this->current === null) {
            return null;
        }

        if ($this->current->type === $type) {
            return $this->chomp($type);
        }

        return null;
    }

    public function ifNextIs(string $type): bool
    {
        $next = $this->next();
        if ($next && $next->type === $type) {
            $this->current = @$this->tokens[++$this->position];
            return true;
        }

        return false;
    }

    /**
     * If the current or next non-whitespace node matches,
     * advance internal pointer and return true;
     */
    public function if(string $type): bool
    {
        if (null === $this->current) {
            return false;
        }

        if ($this->current->type === $type) {
            return true;
        }

        if ($this->current->type !== Token::T_WHITESPACE) {
            return false;
        }

        $next = $this->next();
        if ($next && $this->next()->type === $type) {
            $this->current = $this->tokens[++$this->position];
            return true;
        }

        return false;
    }

    public function next(): ?Token
    {
        if (!isset($this->tokens[$this->position + 1])) {
            return null;
        }

        return $this->tokens[$this->position + 1];
    }
}
