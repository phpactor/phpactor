<?php

namespace Phpactor\DocblockParser\Ast;

class TextNode extends Node
{
    protected const CHILD_NAMES = [
        'tokens',
    ];

    /**
     * @param Token[] $tokens
     */
    public function __construct(public array $tokens)
    {
    }

    public function toString(): string
    {
        return implode('', array_filter(array_map(function (Token $token) {
            if (in_array($token->type, [
                Token::T_PHPDOC_OPEN,
                Token::T_PHPDOC_CLOSE,
                Token::T_ASTERISK,
            ])) {
                return false;
            }
            if (str_contains($token->value, "\n")) {
                return false;
            }
            return $token->value;
        }, $this->tokens)));
    }
}
