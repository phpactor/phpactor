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
        return implode('', array_map(function (Token $token) {
            return $token->value;
        }, $this->tokens));
    }
}
