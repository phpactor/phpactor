<?php

namespace Phpactor\DocblockParser\Ast;

class VariableNode extends Node
{
    protected const CHILD_NAMES = [
        'name'
    ];

    public function __construct(public Token $name)
    {
    }

    public function name(): Token
    {
        return $this->name;
    }
}
