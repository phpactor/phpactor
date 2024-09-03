<?php

namespace Phpactor\DocblockParser\Ast;

use ArrayIterator;
use IteratorAggregate;
use RuntimeException;

/**
 * @implements IteratorAggregate<int, Token>
 */
final class Tokens implements IteratorAggregate
{
    public ?Token $current;

    private int $position = 0;

    /**
     * @param Token[] $tokens
     */
    public function __construct(private array $tokens)
    {
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
                'Expected type "%s" at position "%s": "%s" got "%s"',
                $type,
                $this->position,
                implode('', array_map(function (Token $token) {
                    return $token->value;
                }, $this->tokens)),
                $token->type,
            ));
        }

        return $token;
    }

    public function chompWhitespace(): void
    {
        while ($token = $this->chompIf(Token::T_WHITESPACE, Token::T_ASTERISK)) {
        }
    }

    /**
     * Chomp only if the current node is the given type
     */
    public function chompIf(string ...$types): ?Token
    {
        if ($this->current === null) {
            return null;
        }

        foreach ($types as $type) {
            if ($this->current->type === $type) {
                return $this->chomp($type);
            }
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

    public function ifOneOf(string ...$types): bool
    {
        foreach ($types as $type) {
            if (true === $this->if($type)) {
                return true;
            }
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

        $offset = 0;
        while ($peek = $this->peek($offset)) {
            if (
                $peek->type === Token::T_WHITESPACE ||
                $peek->type === Token::T_ASTERISK
            ) {
                $offset++;
                continue;
            }
            if ($peek->type === $type) {
                $this->current = $peek;
                $this->position += $offset;
                return true;
            }
            return false;
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

    public function peekIs(int $offset, string $type): bool
    {
        $token = $this->peek($offset);

        if ($token && $token->type === $type) {
            return true;
        }

        return false;
    }

    public function peek(int $offset): ?Token
    {
        if (!isset($this->tokens[$this->position + $offset])) {
            return null;
        }

        return $this->tokens[$this->position + $offset];
    }

    public function mustGetCurrent(): Token
    {
        if (!$this->current) {
            throw new RuntimeException(
                'There is no current token (current is NULL)'
            );
        }
        return $this->current;
    }

    public function mustChomp(?string $type = null): Token
    {
        $chomped = $this->chomp($type);
        if (null === $chomped) {
            throw new RuntimeException('Could not chomp, nothing to chomp!');
        }
        return $chomped;
    }
}
