<?php

namespace Phpactor\DocblockParser\Ast;

class UnknownTag extends TagNode
{
    protected const CHILD_NAMES = [
        'name'
    ];
    public function __construct(public Token $name)
    {
    }
}
