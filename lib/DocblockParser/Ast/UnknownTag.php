<?php

namespace Phpactor\DocblockParser\Ast;

class UnknownTag extends TagNode
{
    public function __construct(public Token $name)
    {
    }
}
