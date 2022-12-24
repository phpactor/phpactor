<?php

namespace Phpactor\DocblockParser\Ast;

use Generator;

class Docblock extends Node
{
    protected const CHILD_NAMES = [
        'children'
    ];

    /**
     * @var ElementList<Element>
     */
    public ElementList $children;

    /**
     * @param Element[] $children
     */
    public function __construct(array $children)
    {
        $this->children = new ElementList($children);
    }

    /**
     * @param class-string $tagFqn
     */
    public function hasTag(string $tagFqn): bool
    {
        foreach ($this->tags() as $tag) {
            if ($tag instanceof $tagFqn) {
                return true;
            }
        }

        return false;
    }

    /**
     * @template T of TagNode
     * @param class-string<T>|null $tagFqn
     * @return ($tagFqn is string ? Generator<T> : Generator<TagNode>)
     */
    public function tags(?string $tagFqn = null): Generator
    {
        foreach ($this->children as $child) {
            if ($tagFqn && $child instanceof $tagFqn) {
                yield $child;
                continue;
            }
            if (!$tagFqn && $child instanceof TagNode) {
                yield $child;
                continue;
            }
        }
    }

    public function phpDocOpen(): ?Token {
        foreach ($this->tokens() as $token) {
            if ($token->type === Token::T_PHPDOC_OPEN) {
                return $token;
            }
        }

        return null;
    }

    public function prose(): string
    {
        return trim(implode('', array_map(function (Element $token): string {
            if ($token instanceof Token) {
                if (in_array($token->type, [
                    Token::T_PHPDOC_OPEN,
                    Token::T_PHPDOC_CLOSE,
                    Token::T_ASTERISK
                ])) {
                    return '';
                }
                return $token->value;
            }
            return '';
        }, iterator_to_array($this->children, false))));
    }

    public function lastMultilineContentToken(): ?Token
    {
        $hasLeading = false;
        foreach ($this->tokens() as $child) {
            if ($child->type === Token::T_ASTERISK) {
                $hasLeading = true;
            }
            if ($child->type === Token::T_PHPDOC_CLOSE && $hasLeading) {
                return $lastToken;
            }
            $lastToken = $child;
        }

        return null;
    }

    public function indentationLevel(): int
    {
        $previous = null;
        foreach ($this->children->elements as $child) {
            if (!$child instanceof Token) {
                continue;
            }
            if ($child->type === Token::T_ASTERISK) {
                return $previous->length();
            }
            $previous = $child;
        }

        return 0;

    }
}
